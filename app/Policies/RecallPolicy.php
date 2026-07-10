<?php

namespace App\Policies;

use App\Models\Recall;
use App\Models\User;

class RecallPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('recall.viewAny');
    }

    public function view(User $user, Recall $recall): bool
    {
        return $user->can('recall.view');
    }

    public function create(User $user): bool
    {
        return $user->can('recall.create');
    }

    public function update(User $user, Recall $recall): bool
    {
        return $user->can('recall.update');
    }

    public function delete(User $user, Recall $recall): bool
    {
        return $user->can('recall.delete');
    }

    public function sendReminder(User $user, Recall $recall): bool
    {
        return $user->can('recall.send-reminder');
    }

    public function markCompleted(User $user, Recall $recall): bool
    {
        return $user->can('recall.mark-completed');
    }
}