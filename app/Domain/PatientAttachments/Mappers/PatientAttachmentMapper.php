<?php

namespace App\Domain\PatientAttachments\Mappers;

use App\Domain\PatientAttachments\DTOs\CreatePatientAttachmentDTO;
use App\Domain\PatientAttachments\DTOs\UpdatePatientAttachmentDTO;
use App\Http\Requests\v1\PatientAttachment\StorePatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\UpdatePatientAttachmentRequest;

class PatientAttachmentMapper
{
    public static function fromCreateRequest(StorePatientAttachmentRequest $request): CreatePatientAttachmentDTO
    {
        return new CreatePatientAttachmentDTO(
            userId: (int) $request->validated('user_id'),
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            fileName: $request->validated('file_name'),
            filePath: $request->validated('file_path'),
            fileType: $request->validated('file_type'),
            notes: $request->validated('notes')
        );
    }

    public static function fromUpdateRequest(UpdatePatientAttachmentRequest $request): UpdatePatientAttachmentDTO
    {
        return new UpdatePatientAttachmentDTO(
            userId: $request->validated('user_id') ? (int) $request->validated('user_id') : null,
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            fileName: $request->validated('file_name'),
            filePath: $request->validated('file_path'),
            fileType: $request->validated('file_type'),
            notes: $request->validated('notes')
        );
    }
}
