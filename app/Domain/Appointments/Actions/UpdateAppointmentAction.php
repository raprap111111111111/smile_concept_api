<?php

namespace App\Domain\Appointments\Actions;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use App\Domain\AppointmentReminders\Actions\ScheduleReminderAction;
use App\Domain\AppointmentReminders\Repositories\AppointmentReminderRepository;
use App\Domain\Appointments\DTOs\UpdateAppointmentDTO;
use App\Domain\Appointments\Repositories\AppointmentRepository;
use App\Domain\Appointments\Services\AppointmentService;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentRescheduledNotification;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class UpdateAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository         $repository,
        private readonly AppointmentService            $appointmentService,
        private readonly ActivityLogger                $logger,
        private readonly UpdateAppointmentStatusAction $statusAction, // ✅ delegate status changes
        private readonly AppointmentReminderRepository $reminderRepository,
        private readonly ScheduleReminderAction        $scheduleReminder,
    ) {}

    public function execute(Appointment $appointment, UpdateAppointmentDTO $dto): Appointment
    {
        return DB::transaction(function () use ($appointment, $dto) {

            // ─── 1. Handle status change separately ──────────
            // Status transitions have their own rules & notifications
            // Delegate to UpdateAppointmentStatusAction
            if ($dto->status !== null && $dto->status !== $appointment->status) {
                $this->statusAction->execute(
                    $appointment,
                    $dto->status,
                    $dto->cancellationReason,
                );
            }

            // ─── 2. Check time conflict if time changed ───────
            // ✅ Carbon-aware comparison — start_time/end_time are datetime
            //    casts, so string !== Carbon would always report a change
            $timeChanged = ($dto->startTime && !Carbon::parse($dto->startTime)->equalTo($appointment->start_time))
                        || ($dto->endTime   && !Carbon::parse($dto->endTime)->equalTo($appointment->end_time));

            if ($timeChanged) {
                $startTime = $dto->startTime ?? $appointment->start_time;
                $endTime   = $dto->endTime   ?? $appointment->end_time;
                $doctorId  = $dto->doctorId  ?? $appointment->doctor_id;

                if ($this->repository->checkConflicts(
                    $doctorId,
                    $startTime,
                    $endTime,
                    excludeId: $appointment->id   // ✅ exclude self
                )) {
                    throw ValidationException::withMessages([
                        'start_time' => ['This time slot is already booked for this doctor.'],
                    ]);
                }
            }

            // ─── 3. Build update payload ─────────────────────
            // ✅ Use explicit null check — NOT array_filter (drops false)
            $data = [];

            if (!is_null($dto->userId))             $data['user_id']          = $dto->userId;
            if (!is_null($dto->doctorId))           $data['doctor_id']        = $dto->doctorId;
            if (!is_null($dto->branchId))           $data['branch_id']        = $dto->branchId;
            if (!is_null($dto->startTime))          $data['start_time']       = $dto->startTime;
            if (!is_null($dto->endTime))            $data['end_time']         = $dto->endTime;
            if (!is_null($dto->reasonForVisit))     $data['reason_for_visit'] = $dto->reasonForVisit;
            if (!is_null($dto->reminderSent))       $data['reminder_sent']    = $dto->reminderSent; // ✅ false is valid

            // ✅ Skip status — handled by statusAction above
            // ✅ Skip cancellation_reason — handled by statusAction above

            if (empty($data)) {
                return $appointment->fresh(['user', 'doctor.user', 'branch']); // Nothing to update
            }

            // ─── 4. Persist ──────────────────────────────────
            $updated = $this->repository->update($appointment, $data);

            // ─── 5. Audit log ─────────────────────────────────
            $this->logger->log($updated, 'updated', [
                'changes' => $data,
            ]);

            // ─── 6. Reschedule reminders + send alerts ───────
            if ($timeChanged) {
                // Old reminders point at the previous time — rebuild them
                $this->reminderRepository->cancelPendingForAppointment($updated->id);
                $this->scheduleReminder->execute($updated);

                $this->sendRescheduleAlerts($updated);
            }

            return $updated->load(['user', 'doctor.user', 'branch']);
        });
    }

    /**
     * Alert patient + admins about the reschedule.
     * Admins are alerted only when the change was made by a non-admin,
     * mirroring the cancellation flow.
     */
    private function sendRescheduleAlerts(Appointment $appointment): void
    {
        $notification = new AppointmentRescheduledNotification($appointment);

        $appointment->user?->notify($notification);

        $rescheduledByAdmin = auth()->user()
            ?->hasAnyRole(['admin', 'super-admin', 'owner']) ?? false;

        if (!$rescheduledByAdmin) {
            $admins = $this->getAdmins();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, $notification);
            }
        }
    }

    /**
     * Same admin audience as CreateAppointmentAction.
     *
     * @return Collection<int, User>
     */
    private function getAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', fn($q) => $q->whereIn('name', [
                'admin',
                'super-admin',
                'owner',
            ]))
            ->get();
    }
}