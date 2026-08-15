<?php

namespace App\Domain\Consents\Mappers;

use App\Domain\Consents\DTOs\SignConsentDTO;
use App\Domain\Consents\DTOs\VoidConsentDTO;
use App\Http\Requests\v1\Consent\SignConsentRequest;
use App\Http\Requests\v1\Consent\VoidConsentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ConsentMapper
{
    // ─── Sign ─────────────────────────────────────────────────────────────────

    public static function fromSignRequest(SignConsentRequest $request): SignConsentDTO
    {
        $actor          = $request->user();
        $signOnBehalfOf = $request->validated('sign_on_behalf_of') ?? 'self';
        $rawUserId      = $request->validated('user_id');

        // ── Determine WHO the patient is ──────────────────────────────────
        //
        // Rule: user_id MUST always be a patient account.
        // - If Flutter sent user_id → trust it (it's the picked patient)
        // - Only fall back to actor->id if actor IS a patient AND no user_id
        //
        $patientId = $rawUserId !== null
            ? (int) $rawUserId
            : ($actor->hasRole('patient') ? (int) $actor->id : null);

        if ($patientId === null) {
            throw new \DomainException(
                'No patient specified. Flutter must send user_id when staff is signing.'
            );
        }

        // ── Verify the target user is actually a patient ────────────────
        $targetUser = User::find($patientId);
        if (! $targetUser) {
            throw new \DomainException("Patient [{$patientId}] not found.");
        }

        if (! $targetUser->hasRole('patient')) {
            throw new \DomainException(
                "user_id [{$patientId}] ({$targetUser->name}) is not a patient account. "
                . "Never store staff/admin id as the consent patient."
            );
        }

        // ── Determine WHO physically signed ────────────────────────────
        $signedByStaffId    = null;
        $signedByGuardianId = null;

        switch ($signOnBehalfOf) {
            case 'guardian':
                // Guardian signed → actor is the guardian
                $signedByGuardianId = (int) $actor->id;
                break;

            case 'staff':
                // Staff witnessed → actor is the staff
                $signedByStaffId = (int) $actor->id;
                break;

            case 'self':
            default:
                // Patient signed themselves.
                // If actor is staff (e.g. filling in on kiosk for the patient),
                // record staff as the facilitator but keep patient as user_id.
                if (! $actor->hasRole('patient')) {
                    $signedByStaffId = (int) $actor->id;
                }
                break;
        }

        // ── Debug log ──────────────────────────────────────────────────
        Log::debug('ConsentMapper::fromSignRequest', [
            'actor_id'             => $actor->id,
            'actor_name'           => $actor->name,
            'actor_roles'          => $actor->getRoleNames()->toArray(),
            'raw_user_id_from_req' => $rawUserId,
            'resolved_patient_id'  => $patientId,
            'patient_name'         => $targetUser->name,
            'sign_on_behalf_of'    => $signOnBehalfOf,
            'signed_by_staff_id'   => $signedByStaffId,
            'signed_by_guardian_id' => $signedByGuardianId,
        ]);

        // ── form_data: read raw to prevent Laravel from stripping nested keys ─
        $formData = $request->input('form_data', []);

        return new SignConsentDTO(
            consentTemplateId:  (int) $request->validated('consent_template_id'),
            userId:             $patientId,               // ← ALWAYS the patient
            appointmentId:      $request->validated('appointment_id'),
            signatureData:      $request->validated('signature_data'),
            signedByStaffId:    $signedByStaffId,
            signedByGuardianId: $signedByGuardianId,
            signerRelationship: $signOnBehalfOf,
            formData:           $formData,
            ipAddress:          $request->ip(),
            userAgent:          $request->userAgent(),
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