<?php

namespace App\Domain\Consents\Actions;

use App\Domain\Consents\DTOs\SignConsentDTO;
use App\Models\PatientConsent;
use App\Models\User;

class SignConsentAction
{
    public function execute(SignConsentDTO $dto): PatientConsent
    {
        // Hard guard: user_id must belong to a patient account
        $patient = User::findOrFail($dto->userId);

        if (! $patient->hasRole('patient')) {
            throw new \DomainException(
                "user_id [{$dto->userId}] is not a patient account. " .
                "Never store staff/admin id as the consent patient."
            );
        }

       

        return PatientConsent::create([
            'consent_template_id'   => $dto->consentTemplateId,
            'user_id'               => $dto->userId,
            'appointment_id'        => $dto->appointmentId,
            'signed_by_staff_id'    => $dto->signedByStaffId,
            'signed_by_guardian_id' => $dto->signedByGuardianId,   
            'signer_relationship'   => $dto->signerRelationship,   
            'signed_at'             => now(),
            'signature_data'        => $dto->signatureData,
            'form_data'             => $dto->formData,            
            'ip_address'            => $dto->ipAddress,
            'user_agent'            => $dto->userAgent,
        ]);
    }
}