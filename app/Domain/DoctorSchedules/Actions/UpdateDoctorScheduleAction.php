<?php

namespace App\Domain\DoctorSchedules\Actions;

use App\Domain\DoctorSchedules\DTOs\UpdateDoctorScheduleDTO;
use App\Domain\DoctorSchedules\Repositories\DoctorScheduleRepository;
use App\Domain\DoctorSchedules\Services\DoctorScheduleService;
use App\Models\DoctorSchedule;

class UpdateDoctorScheduleAction
{
    public function __construct(
        private readonly DoctorScheduleRepository $repository,
        private readonly DoctorScheduleService $service
    ) {}

    public function execute(DoctorSchedule $schedule, UpdateDoctorScheduleDTO $dto)
    {
        $startTime = $dto->startTime ?? $schedule->start_time;
        $endTime = $dto->endTime ?? $schedule->end_time;
        $dayOfWeek = $dto->dayOfWeek ?? $schedule->day_of_week->value;
        $doctorId = $dto->doctorId ?? $schedule->doctor_id;

        $this->service->validateTimeInterval($startTime, $endTime);

        if ($this->repository->hasOverlappingSchedule(
            $doctorId,
            $dayOfWeek,
            $startTime,
            $endTime,
            $schedule->id
        )) {
            throw new \Exception("The updated interval overlaps with an existing schedule for this doctor.");
        }

        $data = array_filter([
            'doctor_id' => $dto->doctorId,
            'branch_id' => $dto->branchId,
            'day_of_week' => $dto->dayOfWeek,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
        ], fn($value) => !is_null($value));

        return $this->repository->update($schedule, $data);
    }
}
