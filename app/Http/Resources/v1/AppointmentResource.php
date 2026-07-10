<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id, // ✅ fixed
            'user_id'   => $this->user_id,
            'doctor_id' => $this->doctor_id,
            'branch_id' => $this->branch_id,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'status'     => $this->status,
            'reminder_sent' => (bool) $this->reminder_sent,

            'user' => [
                'id'    => $this->user?->id,
                'name'  => $this->user?->name,
                'email' => $this->user?->email,
            ],

            'doctor' => [
                'id'        => $this->doctor?->id,
                'name'      => $this->doctor?->user?->name,
                'specialty' => $this->doctor?->specialty,
            ],

            'branch' => [
                'id'          => $this->branch?->id,
                'name'        => $this->branch?->name,
                'branch_code' => $this->branch?->branch_code,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
