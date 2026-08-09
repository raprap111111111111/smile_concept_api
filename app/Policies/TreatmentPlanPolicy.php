<?php

namespace App\Policies;

use App\Models\TreatmentPlan;
use App\Models\User;

class TreatmentPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('treatment-plan.viewAny')
            || $user->can('treatment-plan.view');
    }

    public function view(User $user, TreatmentPlan $plan): bool
    {
        if ($user->can('treatment-plan.viewAny')) {
            return true;
        }

        if ($user->can('treatment-plan.view')) {
            return $plan->user_id === $user->id;
        }

        return false;
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

    public function accept(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.accept')
            && $plan->user_id === $user->id;
    }

    public function reject(User $user, TreatmentPlan $plan): bool
    {
        return $user->can('treatment-plan.reject')
            && $plan->user_id === $user->id;
    }
}