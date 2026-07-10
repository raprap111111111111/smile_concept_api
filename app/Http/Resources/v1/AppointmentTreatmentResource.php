<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentTreatmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'appointment_id' => $this->appointment_id,
            'treatment_id'   => $this->treatment_id,
            'tooth_number'   => $this->tooth_number,
            'price_charged'  => $this->price_charged,
            'notes'          => $this->notes,

            'treatment' => $this->whenLoaded('treatment', fn() => [
                'id'    => $this->treatment->id,
                'name'  => $this->treatment->name,
                'price' => $this->treatment->price,
            ]),

            'appointment' => $this->whenLoaded('appointment', fn() => [
                'id'         => $this->appointment->id,
                'start_time' => $this->appointment->start_time,
                'end_time'   => $this->appointment->end_time,
                'status'     => $this->appointment->status,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}