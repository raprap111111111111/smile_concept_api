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
        if (! $user->can('patient.update')) {
            return false;
        }

        // The patient role holds patient.update so it can maintain its own
        // record from the profile page — it must not reach anyone else's.
        if ($user->isPatient()) {
            return $profile->user_id === $user->id;
        }

        return true;
    }

    public function delete(User $user, PatientProfile $profile): bool
    {
        return $user->can('patient.delete');
    }
}