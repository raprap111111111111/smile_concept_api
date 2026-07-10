<?php

namespace App\Domain\DoctorSchedules\Mappers;

use App\Domain\DoctorSchedules\DTOs\CreateDoctorScheduleDTO;
use App\Domain\DoctorSchedules\DTOs\UpdateDoctorScheduleDTO;
use App\Enums\DayOfWeek;
use App\Http\Requests\v1\DoctorSchedule\StoreDoctorScheduleRequest;
use App\Http\Requests\v1\DoctorSchedule\UpdateDoctorScheduleRequest;

class DoctorScheduleMapper
{
    public static function fromCreateRequest(StoreDoctorScheduleRequest $request): CreateDoctorScheduleDTO
    {
        return new CreateDoctorScheduleDTO(
            doctorId: (int) $request->validated('doctor_id'),
            branchId: (int) $request->validated('branch_id'),
            dayOfWeek: DayOfWeek::from((int) $request->day_of_week),
            startTime: $request->validated('start_time'),
            endTime: $request->validated('end_time'),
        );
    }

    public static function fromUpdateRequest(UpdateDoctorScheduleRequest $request): UpdateDoctorScheduleDTO
    {
        return new UpdateDoctorScheduleDTO(
            doctorId: $request->validated('doctor_id') ? (int) $request->validated('doctor_id') : null,
            branchId: $request->validated('branch_id') ? (int) $request->validated('branch_id') : null,
            dayOfWeek: $request->has('day_of_week') ? (int) $request->validated('day_of_week') : null,
            startTime: $request->validated('start_time'),
            endTime: $request->validated('end_time'),
        );
    }
}
