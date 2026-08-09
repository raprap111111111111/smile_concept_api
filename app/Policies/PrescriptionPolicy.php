<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('prescription.viewAny')
            || $user->can('prescription.viewOwn');
    }

    public function view(User $user, Prescription $prescription): bool
    {
        if ($user->can('prescription.view') || $user->can('prescription.viewAny')) {
            return true;
        }

        return $user->can('prescription.viewOwn')
            && $this->owns($user, $prescription);
    }

    public function create(User $user): bool
    {
        return $user->can('prescription.create');
    }

    public function update(User $user, Prescription $prescription): bool
    {
        return $user->can('prescription.update');
    }

    public function delete(User $user, Prescription $prescription): bool
    {
        return $user->can('prescription.delete');
    }

    public function print(User $user, Prescription $prescription): bool
    {
        if ($user->can('prescription.print')) {
            return true;
        }

        return $user->can('prescription.viewOwn')
            && $this->owns($user, $prescription);
    }

    public function send(User $user, Prescription $prescription): bool
    {
        return $user->can('prescription.send');
    }

    private function owns(User $user, Prescription $prescription): bool
    {
        return (int) $prescription->user_id === (int) $user->id;
    }
}
