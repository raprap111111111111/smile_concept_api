<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'total_estimated_amount' => $this->total_estimated_amount,
            'notes' => $this->notes,
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
            ],
            'doctor' => [
                'id' => $this->doctor?->id,
                'name' => $this->doctor?->user?->name,
            ],
            'steps' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'sequence_order' => $item->sequence_order,
                    'treatment_id' => $item->treatment_id,
                    'treatment_name' => $item->treatment?->name,
                    'estimated_cost' => $item->estimated_cost,
                    'notes' => $item->notes,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
