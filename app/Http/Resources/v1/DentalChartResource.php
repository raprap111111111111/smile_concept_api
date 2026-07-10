<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentalChartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'appointment_id' => $this->appointment_id,
            'general_notes' => $this->general_notes,
            'patient' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'entries' => $this->entries->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'tooth_number' => $entry->tooth_number,
                    'tooth_condition_id' => $entry->tooth_condition_id,
                    'condition_slug' => $entry->condition?->slug,
                    'condition_label' => $entry->condition?->label,
                    'condition_color' => $entry->condition?->color_code,
                    'treatment_applied' => $entry->treatment_applied,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
