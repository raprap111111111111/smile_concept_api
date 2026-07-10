<?php

namespace App\Domain\Consents\Actions;

use App\Domain\Consents\DTOs\SignConsentDTO;
use App\Domain\Consents\Repositories\PatientConsentRepository;

class SignConsentAction
{
    public function __construct(
        private readonly PatientConsentRepository $repository
    ) {}

    public function execute(SignConsentDTO $dto)
    {
        return $this->repository->create([
            'consent_template_id' => $dto->consentTemplateId,
            'user_id' => $dto->userId,
            'appointment_id' => $dto->appointmentId,
            'signed_at' => now(),
            'signature_data' => $dto->signatureData,
            'ip_address' => $dto->ipAddress,
            'user_agent' => $dto->userAgent,
        ]);
    }
}
