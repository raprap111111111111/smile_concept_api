<?php

namespace App\Domain\PatientAttachments\Actions;

use App\Domain\PatientAttachments\DTOs\UpdatePatientAttachmentDTO;
use App\Domain\PatientAttachments\Repositories\PatientAttachmentRepository;
use App\Domain\PatientAttachments\Services\PatientAttachmentService;
use App\Models\PatientAttachment;

class UpdatePatientAttachmentAction
{
    public function __construct(
        private readonly PatientAttachmentRepository $repository,
        private readonly PatientAttachmentService $service
    ) {}

    public function execute(PatientAttachment $attachment, UpdatePatientAttachmentDTO $dto)
    {
        if ($dto->fileType !== null) {
            $this->service->validateFileType($dto->fileType);
        }

        $data = array_filter([
            'user_id' => $dto->userId,
            'appointment_id' => $dto->appointmentId,
            'file_name' => $dto->fileName,
            'file_path' => $dto->filePath,
            'file_type' => $dto->fileType,
            'notes' => $dto->notes,
        ], fn($value) => !is_null($value));

        return $this->repository->update($attachment, $data);
    }
}
