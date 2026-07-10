<?php

namespace App\Policies;

use App\Models\DentalChartEntry;
use App\Models\User;

class DentalChartEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dental-chart.viewAny');   // Uses same permission as parent
    }

    public function view(User $user, DentalChartEntry $entry): bool
    {
        return $user->can('dental-chart.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dental-chart.create');
    }

    public function update(User $user, DentalChartEntry $entry): bool
    {
        return $user->can('dental-chart.update');
    }

    public function delete(User $user, DentalChartEntry $entry): bool
    {
        return $user->can('dental-chart.delete');
    }
}