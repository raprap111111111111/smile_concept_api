<?php
// app/Domain/Appointments/Mappers/AppointmentMapper.php

namespace App\Domain\Appointments\Mappers;

use App\Domain\Appointments\DTOs\CreateAppointmentDTO;
use App\Domain\Appointments\DTOs\UpdateAppointmentDTO;
use App\Http\Requests\v1\Appointment\StoreAppointmentRequest;
use App\Http\Requests\v1\Appointment\UpdateAppointmentRequest;
use App\Enums\AppointmentStatus;
use App\Domain\Appointments\DTOs\CalendarCountsAppointmentDTO;
use App\Http\Requests\v1\Appointment\CalendarCountsAppointmentRequest;

class AppointmentMapper
{
    public static function fromCreateRequest(StoreAppointmentRequest $request): CreateAppointmentDTO
    {
        $validated = $request->validated();

        return new CreateAppointmentDTO(
            doctorId: (int) $validated['doctor_id'],
            branchId: (int) $validated['branch_id'],
            startTime: $validated['start_time'],
            endTime: $validated['end_time'],
            status: AppointmentStatus::from($validated['status'] ?? 'pending'),
            userId: isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            createdBy: isset($validated['created_by']) ? (int) $validated['created_by'] : auth()->id(),
            reasonForVisit: $validated['reason_for_visit'] ?? null,  // ✅ ADD
            reminderSent: $validated['reminder_sent'] ?? false,
        );
    }

    public static function fromUpdateRequest(UpdateAppointmentRequest $request): UpdateAppointmentDTO
    {
        $validated = $request->validated();

        return new UpdateAppointmentDTO(
            userId: isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            doctorId: isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null,
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            startTime: $validated['start_time'] ?? null,
            endTime: $validated['end_time'] ?? null,
            status: isset($validated['status']) ? AppointmentStatus::from($validated['status']) : null,
            reasonForVisit: $validated['reason_for_visit'] ?? null,       // ✅ ADD
            cancellationReason: $validated['cancellation_reason'] ?? null,    // ✅ ADD
            reminderSent: $validated['reminder_sent'] ?? null,
        );
    }

    public static function fromCalendarCountsRequest(
        CalendarCountsAppointmentRequest $request
    ): CalendarCountsAppointmentDTO {
        $validated = $request->validated();

        return new CalendarCountsAppointmentDTO(
            month: $validated['month'],
            status: $validated['status'] ?? null,
            doctorId: isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null,
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            userId: isset($validated['user_id']) ? (int) $validated['user_id'] : null,
        );
    }
}
