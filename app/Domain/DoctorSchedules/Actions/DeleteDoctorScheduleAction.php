<?php

namespace App\Domain\DoctorSchedules\Actions;

use App\Domain\DoctorSchedules\Repositories\DoctorScheduleRepository;
use App\Models\DoctorSchedule;

class DeleteDoctorScheduleAction
{
    public function __construct(
        private readonly DoctorScheduleRepository $repository
    ) {}

    public function execute(DoctorSchedule $schedule): bool
    {
        return $this->repository->delete($schedule);
    }
}
