<?php

namespace App\Policies;

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

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.delete');
    }

    public function updateStatus(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.update-status');
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.cancel');
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.approve');
    }
}