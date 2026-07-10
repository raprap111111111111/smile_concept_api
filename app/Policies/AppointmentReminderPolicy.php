<?php

namespace App\Policies;

use App\Models\AppointmentReminder;
use App\Models\User;

class AppointmentReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reminder.viewAny');
    }

    public function view(User $user, AppointmentReminder $reminder): bool
    {
        return $user->can('reminder.view');
    }

    /**
     * Reminders are auto-scheduled on appointment creation.
     */
    public function create(User $user): bool
    {
        return $user->can('reminder.create');
    }

    public function update(User $user, AppointmentReminder $reminder): bool
    {
        return $user->can('reminder.update');
    }

    public function delete(User $user, AppointmentReminder $reminder): bool
    {
        return $user->can('reminder.delete');
    }

    public function send(User $user, AppointmentReminder $reminder): bool
    {
        return $user->can('reminder.send');
    }
}