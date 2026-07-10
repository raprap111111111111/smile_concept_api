<?php

namespace App\Domain\Notifications\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public function sendToUser(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /**
     * Send a notification to multiple users.
     *
     * @param  iterable<User>  $users
     */
    public function sendToMany(iterable $users, Notification $notification): void
    {
        NotificationFacade::send($users, $notification);
    }
}