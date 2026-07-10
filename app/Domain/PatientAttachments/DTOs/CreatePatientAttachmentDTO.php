<?php

namespace App\Domain\PatientAttachments\DTOs;

final readonly class CreatePatientAttachmentDTO
{
    public function __construct(
        public int $userId,
        public ?int $appointmentId,
        public string $fileName,
        public string $filePath,
        public string $fileType,
        public ?string $notes = null
    ) {}
}
