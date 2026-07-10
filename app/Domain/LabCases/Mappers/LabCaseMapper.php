<?php

namespace App\Domain\LabCases\Mappers;

use App\Domain\LabCases\DTOs\CreateLabCaseDTO;
use App\Domain\LabCases\DTOs\UpdateLabCaseDTO;
use App\Http\Requests\v1\LabCase\StoreLabCaseRequest;
use App\Http\Requests\v1\LabCase\UpdateLabCaseRequest;

class LabCaseMapper
{
    public static function fromCreateRequest(StoreLabCaseRequest $request): CreateLabCaseDTO
    {
        return new CreateLabCaseDTO(
            appointmentId: (int) $request->validated('appointment_id'),
            labName: $request->validated('lab_name'),
            workType: $request->validated('work_type'),
            status: $request->validated('status', 'sent'),
            sentDate: $request->validated('sent_date'),
            dueDate: $request->validated('due_date'),
            receivedDate: $request->validated('received_date'),
            cost: $request->has('cost') ? (float) $request->validated('cost') : null,
            notes: $request->validated('notes')
        );
    }

    public static function fromUpdateRequest(UpdateLabCaseRequest $request): UpdateLabCaseDTO
    {
        return new UpdateLabCaseDTO(
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            labName: $request->validated('lab_name'),
            workType: $request->validated('work_type'),
            status: $request->validated('status'),
            sentDate: $request->validated('sent_date'),
            dueDate: $request->validated('due_date'),
            receivedDate: $request->validated('received_date'),
            cost: $request->has('cost') ? (float) $request->validated('cost') : null,
            notes: $request->validated('notes')
        );
    }
}
