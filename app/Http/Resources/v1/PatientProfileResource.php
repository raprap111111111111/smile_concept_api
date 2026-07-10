<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'user_id' => $this->user_id,

            // Medical
            'allergies'       => $this->allergies,
            'medical_history' => $this->medical_history,

            // ✅ Null-safe enum
            'blood_type' => $this->blood_type instanceof \BackedEnum
                ? $this->blood_type->value
                : $this->blood_type,

            // Emergency
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,

            // Medical alerts
            'requires_epinephrine_free_anesthesia' => (bool) $this->requires_epinephrine_free_anesthesia,
            'has_cardiac_conditions'               => (bool) $this->has_cardiac_conditions,
            'is_pregnant'                          => (bool) $this->is_pregnant,
            'has_bleeding_disorders'               => (bool) $this->has_bleeding_disorders,

            // User (patient)
            'patient' => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}