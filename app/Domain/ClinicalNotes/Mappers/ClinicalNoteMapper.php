<?php

namespace App\Domain\ClinicalNotes\Mappers;

use App\Domain\ClinicalNotes\DTOs\CreateClinicalNoteDTO;
use App\Domain\ClinicalNotes\DTOs\UpdateClinicalNoteDTO;
use App\Http\Requests\v1\ClinicalNote\StoreClinicalNoteRequest;
use App\Http\Requests\v1\ClinicalNote\UpdateClinicalNoteRequest;

class ClinicalNoteMapper
{
    public static function fromCreateRequest(StoreClinicalNoteRequest $request): CreateClinicalNoteDTO
    {
        return new CreateClinicalNoteDTO(
            appointmentId: (int) $request->validated('appointment_id'),
            doctorId: (int) $request->validated('doctor_id'),
            treatmentNotes: $request->validated('treatment_notes'),
            postOpInstructions: $request->validated('post_op_instructions'),
            isLocked: (bool) $request->validated('is_locked', false)
        );
    }

    public static function fromUpdateRequest(UpdateClinicalNoteRequest $request): UpdateClinicalNoteDTO
    {
        return new UpdateClinicalNoteDTO(
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            doctorId: $request->validated('doctor_id') ? (int) $request->validated('doctor_id') : null,
            treatmentNotes: $request->validated('treatment_notes'),
            postOpInstructions: $request->validated('post_op_instructions'),
            isLocked: $request->has('is_locked') ? (bool) $request->validated('is_locked') : null
        );
    }
}
