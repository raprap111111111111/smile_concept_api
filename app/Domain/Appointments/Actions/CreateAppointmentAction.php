<?php

namespace App\Domain\Appointments\Actions;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use App\Domain\AppointmentReminders\Actions\ScheduleReminderAction;
use App\Domain\Appointments\DTOs\CreateAppointmentDTO;
use App\Domain\Appointments\Repositories\AppointmentRepository;
use App\Domain\Appointments\Services\AppointmentService;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CreateAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository  $repository,
        private readonly AppointmentService     $appointmentService,
        private readonly ScheduleReminderAction $scheduleReminder,
        private readonly ActivityLogger         $logger,
    ) {}

    public function execute(CreateAppointmentDTO $dto): Appointment
    {
        $authUser = auth()->user();

        if (!$authUser) {
            throw new AuthorizationException('Unauthenticated.');
        }

        /*
        |--------------------------------------------------------------------------
        | Appointment ownership logic
        |--------------------------------------------------------------------------
        |
        | user_id    = patient who owns/receives the appointment
        | created_by = logged-in user who created the appointment
        |
        | Example:
        | Super Admin books for Ryan:
        | user_id    = Ryan's user ID
        | created_by = Super Admin's user ID
        */

        $targetUserId = $dto->userId ?? $authUser->id;

        $isCreatingForSelf = (int) $targetUserId === (int) $authUser->id;

        if ($isCreatingForSelf) {
            if (!$authUser->can('appointment.create')) {
                throw new AuthorizationException(
                    'You are not allowed to create appointments.'
                );
            }
        } else {
            if (!$authUser->can('appointment.create-for-others')) {
                throw new AuthorizationException(
                    'You are not allowed to create appointments for other patients.'
                );
            }
        }

        // ─── 1. Validate appointment time ─────────────────────────────
        $this->appointmentService->validateAppointmentTime($dto);

        // ─── 2. Check doctor conflicts ────────────────────────────────
        if ($this->repository->checkConflicts(
            $dto->doctorId,
            $dto->startTime,
            $dto->endTime
        )) {
            throw ValidationException::withMessages([
                'start_time' => ['The selected time slot is already booked for this doctor.'],
                'end_time'   => ['The selected time slot is already booked for this doctor.'],
            ]);
        }

        // ─── 3. Transaction: create + reminders + audit ───────────────
        $appointment = DB::transaction(function () use ($dto, $authUser, $targetUserId) {
            /** @var Appointment $appointment */
            $appointment = $this->repository->create([
                'user_id'          => $targetUserId,
                'doctor_id'        => $dto->doctorId,
                'branch_id'        => $dto->branchId,
                'start_time'       => $dto->startTime,
                'end_time'         => $dto->endTime,
                'status'           => $dto->status->value,
                'reason_for_visit' => $dto->reasonForVisit,
                'additional_notes' => $dto->additionalNotes,
                'patient_name'     => $dto->patientName,
                'patient_phone'    => $dto->patientPhone,
                'patient_email'    => $dto->patientEmail,
                'created_by'       => $dto->createdBy ?? $authUser->id,
                'reminder_sent'    => $dto->reminderSent ?? false,
            ]);

            // Auto-schedule reminders
            $this->scheduleReminder->execute($appointment);

            // Audit log
            $this->logger->log($appointment, 'created');

            return $appointment;
        });

        // ─── 4. Reload relations ──────────────────────────────────────
        $appointment->load([
            'user',
            'doctor.user',
            'branch',
            'creator',
        ]);

        // ─── 5. Notify patient + admins ───────────────────────────────
        $this->sendNotifications($appointment);

        return $appointment;
    }

    private function sendNotifications(Appointment $appointment): void
    {
        $notification = new AppointmentBookedNotification($appointment);

        // Notify patient
        $appointment->user?->notify($notification);

        // Notify admins / owners
        $admins = $this->getAdmins();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function getAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'admin',
                    'super-admin',
                    'owner',
                ]);
            })
            ->get();
    }
}