<?php

namespace App\Domain\Consents\Mappers;

use App\Domain\Consents\DTOs\SignConsentDTO;
use App\Domain\Consents\DTOs\VoidConsentDTO;
use App\Http\Requests\v1\Consent\SignConsentRequest;   // ✅ Http namespace
use App\Http\Requests\v1\Consent\VoidConsentRequest;

class ConsentMapper
{
    // ─── Sign ─────────────────────────────────────────────────────────────────

    public static function fromSignRequest(SignConsentRequest $request): SignConsentDTO
    {
        $actor = $request->user();

        /*
         * user_id  = the PATIENT this consent is FOR.
         * If actor is a patient, force their own id (cannot sign for others).
         * If actor is staff/admin, use the patient id sent in the payload.
         */
        $patientId = $actor->hasRole('patient')
            ? (int) $actor->id
            : (int) $request->validated('user_id');

        /*
         * signed_by_staff_id = the clinic actor who facilitated signing.
         * Null when the patient signs themselves.
         */
        $signedByStaffId = $actor->hasRole('patient')
            ? null
            : (int) $actor->id;

        return new SignConsentDTO(
            consentTemplateId: (int) $request->validated('consent_template_id'),
            userId:            $patientId,          // PATIENT — never staff ✓
            appointmentId:     $request->validated('appointment_id'),
            signatureData:     $request->validated('signature_data'),
            signedByStaffId:   $signedByStaffId,
            ipAddress:         $request->ip(),
            userAgent:         $request->userAgent(),
        );
    }

    // ─── Void ─────────────────────────────────────────────────────────────────

    public static function fromVoidRequest(VoidConsentRequest $request): VoidConsentDTO
    {
        return new VoidConsentDTO(
            reason:    $request->validated('reason'),
            voidedBy:  (int) $request->user()->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }
}