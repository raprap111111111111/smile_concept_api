<?php
// CreatePatientAttachmentDTO.php
namespace App\Domain\PatientAttachments\DTOs;

class CreatePatientAttachmentDTO
{
    public function __construct(
        public readonly int     $userId,
        public readonly ?int    $appointmentId,
        public readonly string  $fileName,
        public readonly string  $filePath,
        public readonly string  $fileType,
        public readonly string  $category,
        public readonly bool    $isXray,
        public readonly ?string $notes,
    ) {}
}