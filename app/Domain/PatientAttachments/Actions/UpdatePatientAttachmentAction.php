<?php
// UpdatePatientAttachmentAction.php
namespace App\Domain\PatientAttachments\Actions;

use App\Domain\PatientAttachments\DTOs\UpdatePatientAttachmentDTO;
use App\Models\PatientAttachment;

class UpdatePatientAttachmentAction
{
    public function execute(PatientAttachment $attachment, UpdatePatientAttachmentDTO $dto): PatientAttachment
    {
        $attachment->update(array_filter([
            'user_id'        => $dto->userId,
            'appointment_id' => $dto->appointmentId,
            'file_name'      => $dto->fileName,
            'file_path'      => $dto->filePath,
            'file_type'      => $dto->fileType,
            'category'       => $dto->category,
            'is_xray'        => $dto->isXray,
            'notes'          => $dto->notes,
        ], fn($v) => !is_null($v)));

        return $attachment->fresh();
    }
}