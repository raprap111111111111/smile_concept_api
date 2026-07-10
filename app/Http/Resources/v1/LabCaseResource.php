<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabCaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'lab_name' => $this->lab_name,
            'work_type' => $this->work_type,
            'status' => $this->status,
            'sent_date' => $this->sent_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'received_date' => $this->received_date?->toDateString(),
            'cost' => $this->cost,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
