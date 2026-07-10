<?php

namespace App\Policies;

use App\Models\AppointmentTreatment;
use App\Models\User;

class AppointmentTreatmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('appointment-treatment.viewAny');
    }

    public function view(User $user, AppointmentTreatment $item): bool
    {
        return $user->can('appointment-treatment.view');
    }

    public function create(User $user): bool
    {
        return $user->can('appointment-treatment.create');
    }

    public function update(User $user, AppointmentTreatment $item): bool
    {
        return $user->can('appointment-treatment.update');
    }

    public function delete(User $user, AppointmentTreatment $item): bool
    {
        return $user->can('appointment-treatment.delete');
    }
}