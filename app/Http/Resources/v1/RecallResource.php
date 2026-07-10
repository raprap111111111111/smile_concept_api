<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'recall_type_id' => $this->recall_type_id,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'last_notified_at' => $this->last_notified_at?->toDateTimeString(),
            
            // Nested Dynamic Recall Type Details
            'recall_type' => [
                'id' => $this->recallType?->id,
                'slug' => $this->recallType?->slug,
                'label' => $this->recallType?->label,
                'frequency_months' => $this->recallType?->frequency_months,
            ],

            // Nested Patient details
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
                'email' => $this->patient?->email,
                'phone' => $this->patient?->phone,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
