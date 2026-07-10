<?php

namespace App\Policies;

use App\Models\DentalChart;
use App\Models\User;

class DentalChartPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dental-chart.viewAny');
    }

    public function view(User $user, DentalChart $dentalChart): bool
    {
        return $user->can('dental-chart.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dental-chart.create');
    }

    public function update(User $user, DentalChart $dentalChart): bool
    {
        return $user->can('dental-chart.update');
    }

    public function delete(User $user, DentalChart $dentalChart): bool
    {
        return $user->can('dental-chart.delete');
    }
}