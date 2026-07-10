<?php

namespace App\Domain\PatientAttachments\DTOs;

final readonly class UpdatePatientAttachmentDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?int $appointmentId = null,
        public ?string $fileName = null,
        public ?string $filePath = null,
        public ?string $fileType = null,
        public ?string $notes = null
    ) {}
}
