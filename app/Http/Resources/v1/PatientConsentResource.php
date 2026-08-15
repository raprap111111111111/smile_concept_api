<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientConsentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'signed_at'      => $this->signed_at,

            'is_voided'      => $this->resource->isVoided(),
            'is_valid'       => $this->resource->isValid(),

            'voided_at'      => $this->voided_at,
            'voided_reason'  => $this->voided_reason,

            // ✅ NEW — signer role tracking
            'signer_relationship' => $this->signer_relationship ?? null,

            // ✅ NEW — expose captured form answers
            'form_data'      => $this->form_data,

            'template' => $this->whenLoaded('template', fn () => [
                'id'    => $this->template->id,
                'title' => $this->template->title,
                'body'  => $this->template->body,
            ]),

            'patient' => $this->whenLoaded('patient', fn () => [
                'id'   => $this->patient->id,
                'name' => $this->patient->name,
            ]),

            'appointment' => $this->whenLoaded(
                'appointment',
                fn () => $this->appointment ? [
                    'id'           => $this->appointment->id,
                    'scheduled_at' => $this->appointment->scheduled_at ?? null,
                ] : null,
            ),

            'signed_by_staff' => $this->whenLoaded(
                'signedByStaff',
                fn () => $this->signedByStaff ? [
                    'id'   => $this->signedByStaff->id,
                    'name' => $this->signedByStaff->name,
                ] : null,
            ),

            // ✅ NEW — guardian info (if signed by guardian)
            'signed_by_guardian' => $this->whenLoaded(
                'signedByGuardian',
                fn () => $this->signedByGuardian ? [
                    'id'   => $this->signedByGuardian->id,
                    'name' => $this->signedByGuardian->name,
                ] : null,
            ),

            // Signature — only on show / sign response
            'signature_data' => $this->when(
                $request->routeIs('*.show') || $request->routeIs('*.sign'),
                $this->signature_data,
            ),

            'ip_address' => $this->when(
                $request->user()?->can('consent-form.viewAny'),
                $this->ip_address,
            ),

            'created_at' => $this->created_at,
        ];
    }
}