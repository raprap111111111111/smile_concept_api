<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientConsentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_title' => $this->template?->title,
            'template_body' => $this->template?->body,
            'signed_at' => $this->signed_at->toDateTimeString(),
            'ip_address' => $this->ip_address,
            'signature_preview' => $this->signature_data, // Displays the signed signature
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
            ],
        ];
    }
}
