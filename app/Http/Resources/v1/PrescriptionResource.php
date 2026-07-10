<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'doctor_id' => $this->doctor_id,
            'user_id' => $this->user_id, // Patient ID
            'notes' => $this->notes,
            
            // Nested Doctor Profile
            'doctor' => [
                'id' => $this->doctor?->id,
                'license_number' => $this->doctor?->license_number,
                'specialty' => $this->doctor?->specialization,
                'name' => $this->doctor?->user?->name,
            ],

            // Nested Patient Profile
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
                'email' => $this->patient?->email,
                'phone' => $this->patient?->phone,
            ],

            // Nested Medication items
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'medicine_name' => $item->medicine_name,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'duration_days' => $item->duration_days,
                    'instructions' => $item->instructions,
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
