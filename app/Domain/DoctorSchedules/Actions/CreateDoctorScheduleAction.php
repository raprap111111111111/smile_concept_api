<?php

namespace App\Domain\DoctorSchedules\Actions;

use App\Domain\DoctorSchedules\DTOs\CreateDoctorScheduleDTO;
use App\Domain\DoctorSchedules\Repositories\DoctorScheduleRepository;
use App\Domain\DoctorSchedules\Services\DoctorScheduleService;

class CreateDoctorScheduleAction
{
    public function __construct(
        private readonly DoctorScheduleRepository $repository,
        private readonly DoctorScheduleService $service
    ) {}

    public function execute(CreateDoctorScheduleDTO $dto)
    {
        $this->service->validateTimeInterval($dto->startTime, $dto->endTime);

        if ($this->repository->hasOverlappingSchedule(
            $dto->doctorId,
            $dto->dayOfWeek,
            $dto->startTime,
            $dto->endTime
        )) {
            throw new \Exception("This schedule interval overlaps with an existing schedule for this doctor.");
        }

        return $this->repository->create([
            'doctor_id' => $dto->doctorId,
            'branch_id' => $dto->branchId,
            'day_of_week' => $dto->dayOfWeek->value,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
        ]);
    }
}
