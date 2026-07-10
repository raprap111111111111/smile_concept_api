<?php

namespace App\Policies;

use App\Models\LabCase;
use App\Models\User;

class LabCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('lab-case.viewAny');
    }

    public function view(User $user, LabCase $labCase): bool
    {
        return $user->can('lab-case.view');
    }

    public function create(User $user): bool
    {
        return $user->can('lab-case.create');
    }

    public function update(User $user, LabCase $labCase): bool
    {
        return $user->can('lab-case.update');
    }

    public function delete(User $user, LabCase $labCase): bool
    {
        return $user->can('lab-case.delete');
    }
}