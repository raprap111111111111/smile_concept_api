<?php

namespace App\Policies;

use App\Models\Treatment;
use App\Models\User;

class TreatmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('treatment.viewAny');
    }

    public function view(User $user, Treatment $treatment): bool
    {
        return $user->can('treatment.view');
    }

    public function create(User $user): bool
    {
        return $user->can('treatment.create');
    }

    public function update(User $user, Treatment $treatment): bool
    {
        return $user->can('treatment.update');
    }

    public function delete(User $user, Treatment $treatment): bool
    {
        return $user->can('treatment.delete');
    }
}