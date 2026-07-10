<?php

namespace App\Policies;

use App\Models\ToothCondition;
use App\Models\User;

class ToothConditionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dental-chart.viewAny');
    }

    public function view(User $user, ToothCondition $condition): bool
    {
        return $user->can('dental-chart.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dental-chart.create');
    }

    public function update(User $user, ToothCondition $condition): bool
    {
        return $user->can('dental-chart.update');
    }

    public function delete(User $user, ToothCondition $condition): bool
    {
        return $user->can('dental-chart.delete');
    }
}