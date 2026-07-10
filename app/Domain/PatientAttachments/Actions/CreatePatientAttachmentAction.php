<?php

namespace App\Domain\PatientAttachments\Actions;

use App\Domain\PatientAttachments\DTOs\CreatePatientAttachmentDTO;
use App\Domain\PatientAttachments\Repositories\PatientAttachmentRepository;
use App\Domain\PatientAttachments\Services\PatientAttachmentService;

class CreatePatientAttachmentAction
{
    public function __construct(
        private readonly PatientAttachmentRepository $repository,
        private readonly PatientAttachmentService $service
    ) {}

    public function execute(CreatePatientAttachmentDTO $dto)
    {
        $this->service->validateFileType($dto->fileType);

        return $this->repository->create([
            'user_id' => $dto->userId,
            'appointment_id' => $dto->appointmentId,
            'file_name' => $dto->fileName,
            'file_path' => $dto->filePath,
            'file_type' => $dto->fileType,
            'notes' => $dto->notes,
        ]);
    }
}
