<?php

namespace App\Domain\PatientAttachments\Services;

class PatientAttachmentService
{
    public function validateFileType(string $type): void
    {
        $allowed = ['xray', 'photo', 'document'];
        if (!in_array($type, $allowed)) {
            throw new \InvalidArgumentException("Invalid file type. Must be xray, photo, or document.");
        }
    }
}
