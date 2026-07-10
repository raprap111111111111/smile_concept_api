<?php

namespace App\Domain\Notifications\Actions;

use App\Models\User;

class MarkAllAsReadAction
{
    public function execute(User $user): int
    {
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();
        return $count;
    }
}