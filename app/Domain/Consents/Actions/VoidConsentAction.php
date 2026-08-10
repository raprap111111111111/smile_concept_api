<?php

namespace App\Domain\Consents\Actions;

use App\Domain\Consents\DTOs\VoidConsentDTO;
use App\Models\PatientConsent;

class VoidConsentAction
{
    public function execute(PatientConsent $consent, VoidConsentDTO $dto): PatientConsent
    {
        if ($consent->isVoided()) {
            throw new \DomainException('This consent has already been voided.');
        }

        $consent->update([
            'voided_at'     => now(),
            'voided_reason' => $dto->reason,
            'voided_by'     => $dto->voidedBy,
        ]);

        return $consent->fresh();
    }
}