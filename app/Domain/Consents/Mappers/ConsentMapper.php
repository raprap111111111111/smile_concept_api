<?php

namespace App\Domain\Consents\Mappers;

use App\Domain\Consents\DTOs\SignConsentDTO;
use App\Http\Requests\v1\Consent\SignConsentRequest;

class ConsentMapper
{
    public static function fromSignRequest(SignConsentRequest $request): SignConsentDTO
    {
        return new SignConsentDTO(
            consentTemplateId: (int) $request->validated('consent_template_id'),
            userId: (int) $request->validated('user_id'),
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            signatureData: $request->validated('signature_data'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );
    }
}
