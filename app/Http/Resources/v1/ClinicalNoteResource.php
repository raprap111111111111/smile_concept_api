<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicalNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'doctor_id' => $this->doctor_id,
            'treatment_notes' => $this->treatment_notes,
            'post_op_instructions' => $this->post_op_instructions,
            'is_locked' => (bool) $this->is_locked,
            'doctor' => [
                'id' => $this->doctor?->id,
                'name' => $this->doctor?->user?->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
