<?php

namespace App\Policies;

use App\Models\TreatmentPlan;
use App\Models\User;

class TreatmentPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('treatment-plan.viewAny');
    }

    public function view(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.view');
    }

    public function create(User $user): bool
    {
        return $user->can('treatment-plan.create');
    }

    public function update(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.update');
    }

    public function delete(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.delete');
    }

    public function sendToPatient(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.send-to-patient');
    }

    public function accept(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.accept');
    }

    public function reject(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.reject');
    }
}