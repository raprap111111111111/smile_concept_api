<?php

namespace App\Policies;

use App\Models\DoctorSchedule;
use App\Models\User;

class DoctorSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('doctor-schedule.viewAny');
    }

    public function view(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('doctor-schedule.view');
    }

    public function create(User $user): bool
    {
        return $user->can('doctor-schedule.create');
    }

    public function update(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('doctor-schedule.update');
    }

    public function delete(User $user, DoctorSchedule $schedule): bool
    {
        return $user->can('doctor-schedule.delete');
    }
}