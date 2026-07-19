<?php

namespace App\Domain\Appointments\Actions;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use App\Domain\AppointmentReminders\Repositories\AppointmentReminderRepository;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\AppointmentCancelledNotification;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class UpdateAppointmentStatusAction
{
    public function __construct(
        private readonly ActivityLogger                $logger,
        private readonly AppointmentReminderRepository $reminderRepository,
    ) {}

    /**
     * Update an appointment's status with transition validation.
     *
     * @throws DomainException When the status transition is not allowed
     */
    public function execute(
        Appointment              $appointment,
        AppointmentStatus|string $newStatus,
        ?string                  $cancellationReason = null,
    ): Appointment {

        // ─── 1. Normalize to enum ────────────────────────────
        if (is_string($newStatus)) {
            $newStatus = AppointmentStatus::from($newStatus);
        }

        // ─── 2. Resolve previous status ─────────────────────
        $previousStatus = $appointment->status instanceof AppointmentStatus
            ? $appointment->status
            : AppointmentStatus::from($appointment->status);

        // ─── 3. Validate transition ──────────────────────────
        $this->validateTransition($previousStatus, $newStatus);

        // ─── 4. Build update payload ─────────────────────────
        $updateData = ['status' => $newStatus->value];

        if ($newStatus === AppointmentStatus::CANCELLED) {
            if (empty($cancellationReason)) {
                throw new DomainException(
                    'Cancellation reason is required when cancelling an appointment.'
                );
            }
            $updateData['cancellation_reason'] = $cancellationReason;
        }

        // ─── 5. Persist ──────────────────────────────────────
        $appointment->update($updateData);

        // ─── 5b. Cancel pending reminders on cancellation ────
        // Prevents reminder alerts firing for a cancelled appointment
        if ($newStatus === AppointmentStatus::CANCELLED) {
            $this->reminderRepository->cancelPendingForAppointment($appointment->id);
        }

        // ─── 6. Audit log ─────────────────────────────────────
        $this->logger->log($appointment, 'status_changed', [
            'from'   => $previousStatus->value,
            'to'     => $newStatus->value,
            'reason' => $cancellationReason,
        ]);

        // ─── 7. Reload fresh relations ───────────────────────
        $appointment = $appointment->fresh(['user', 'doctor.user', 'branch']);

        // ─── 8. Dispatch notifications ───────────────────────
        $this->dispatchNotifications($appointment, $previousStatus, $newStatus, $cancellationReason);

        return $appointment;
    }

    /**
     * Fire the right notification based on the target status.
     */
    private function dispatchNotifications(
        Appointment       $appointment,
        AppointmentStatus $from,
        AppointmentStatus $to,
        ?string           $reason,
    ): void {
        $patient = $appointment->user;
        $admins  = $this->getAdmins();

        match ($to) {

            AppointmentStatus::CONFIRMED => $patient?->notify(
                new AppointmentBookedNotification($appointment)
            ),

            AppointmentStatus::CANCELLED => $this->notifyCancellation(
                $appointment, $patient, $admins, $reason
            ),

            AppointmentStatus::COMPLETED,
            AppointmentStatus::PENDING   => null,
        };
    }

    /**
     * Fetch admin users using explicit whereHas query.
     * Same admin audience as CreateAppointmentAction.
     * Avoids Intelephense "Undefined method 'role'" for Spatie's dynamic scope.
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

    /**
     * Send cancellation notifications to relevant parties.
     */
    private function notifyCancellation(
        Appointment $appointment,
        ?User       $patient,
        Collection  $admins,
        ?string     $reason,
    ): void {
        $notification = new AppointmentCancelledNotification($appointment, $reason);

        // Notify patient
        if ($patient) {
            $patient->notify($notification);
        }

        // Notify admins only if cancellation was by a non-admin (patient)
        $cancelledByAdmin = auth()->user()
            ?->hasAnyRole(['admin', 'super-admin', 'owner']) ?? false;

        if (!$cancelledByAdmin && $admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }
    }

    /**
     * Define allowed status transitions.
     *
     *   PENDING   → CONFIRMED | CANCELLED
     *   CONFIRMED → COMPLETED | CANCELLED
     *   CANCELLED → (terminal)
     *   COMPLETED → (terminal)
     *
     * @throws DomainException
     */
    private function validateTransition(
        AppointmentStatus $from,
        AppointmentStatus $to,
    ): void {
        if ($from === $to) {
            return; // no-op
        }

        $allowed = match ($from) {
            AppointmentStatus::PENDING   => [
                AppointmentStatus::CONFIRMED,
                AppointmentStatus::CANCELLED,
            ],
            AppointmentStatus::CONFIRMED => [
                AppointmentStatus::COMPLETED,
                AppointmentStatus::CANCELLED,
            ],
            AppointmentStatus::CANCELLED,
            AppointmentStatus::COMPLETED => [],
        };

        if (!in_array($to, $allowed, strict: true)) {
            throw new DomainException(
                "Cannot transition appointment from '{$from->value}' to '{$to->value}'."
            );
        }
    }
}