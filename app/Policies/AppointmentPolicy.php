<?php

namespace App\Policies;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * List ALL appointments
     * Only admin/staff
     */
    public function viewAny(User $user): bool
    {
        return $user->can('appointment.viewAny');
    }

    /**
     * View a SINGLE appointment detail
     * - Admin/Staff (viewAny) → can open any appointment
     * - Patient (no viewAny) → can only open their OWN appointment
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // Admin/Staff can view any single appointment
        if ($user->can('appointment.viewAny')) {
            return true;
        }

        // Patient can only view their own appointment detail
        return (int) $appointment->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('appointment.create');
    }

    public function createForOthers(User $user): bool
    {
        return $user->can('appointment.create-for-others');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.update');
    }

    /**
     * Reschedule an appointment
     * - Staff with appointment.update → any appointment
     * - Patient with appointment.reschedule → own PENDING/CONFIRMED only
     */
    public function reschedule(User $user, Appointment $appointment): bool
    {
        if ($user->can('appointment.update')) {
            return true;
        }

        if (!$user->can('appointment.reschedule')) {
            return false;
        }

        if ((int) $appointment->user_id !== (int) $user->id) {
            return false;
        }

        $status = $appointment->status instanceof AppointmentStatus
            ? $appointment->status
            : AppointmentStatus::from($appointment->status);

        return in_array($status, [
            AppointmentStatus::PENDING,
            AppointmentStatus::CONFIRMED,
        ], strict: true);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.delete');
    }

    public function updateStatus(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.update-status');
    }

    /**
     * Cancel an appointment
     * - Staff with appointment.update-status → any appointment
     * - Patient with appointment.cancel → own PENDING/CONFIRMED only
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->can('appointment.update-status')) {
            return true;
        }

        if (!$user->can('appointment.cancel')) {
            return false;
        }

        if ((int) $appointment->user_id !== (int) $user->id) {
            return false;
        }

        $status = $appointment->status instanceof AppointmentStatus
            ? $appointment->status
            : AppointmentStatus::from($appointment->status);

        return in_array($status, [
            AppointmentStatus::PENDING,
            AppointmentStatus::CONFIRMED,
        ], strict: true);
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.approve');
    }
}