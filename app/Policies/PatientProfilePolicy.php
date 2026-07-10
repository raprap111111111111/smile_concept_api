<?php

namespace App\Policies;

use App\Models\PatientProfile;
use App\Models\User;

class PatientProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patient.viewAny');
    }

    public function view(User $user, PatientProfile $profile): bool
    {
        return $user->can('patient.view');
    }

    public function create(User $user): bool
    {
        return $user->can('patient.create');
    }

    public function update(User $user, PatientProfile $profile): bool
    {
        return $user->can('patient.update');
    }

    public function delete(User $user, PatientProfile $profile): bool
    {
        return $user->can('patient.delete');
    }
}