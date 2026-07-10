<?php

namespace App\Domain\Consents\DTOs;

final readonly class SignConsentDTO
{
    public function __construct(
        public int $consentTemplateId,
        public int $userId,
        public ?int $appointmentId,
        public string $signatureData,
        public ?string $ipAddress,
        public ?string $userAgent
    ) {}
}
