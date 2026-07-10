<?php

namespace App\Domain\Notifications\Actions;

use App\Models\User;

class MarkAsReadAction
{
    public function execute(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }
}