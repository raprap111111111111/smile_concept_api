<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Users can always view their own notifications.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * A user can only view THEIR own notification.
     */
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id
            && $notification->notifiable_type === $user::class;
    }

    /**
     * A user can only update (mark as read) their own notification.
     */
    public function update(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id
            && $notification->notifiable_type === $user::class;
    }

    /**
     * A user can only delete their own notification.
     */
    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id
            && $notification->notifiable_type === $user::class;
    }

    /**
     * Only admins can broadcast/send system-wide notifications.
     */
    public function send(User $user): bool
    {
        return $user->can('notification.create');
    }
}