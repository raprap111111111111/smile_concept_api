<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentReminderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'appointment_id' => $this->appointment_id,
            'channel'        => $this->channel,
            'status'         => $this->status,
            'scheduled_for'  => $this->scheduled_for?->toDateTimeString(),
            'sent_at'        => $this->sent_at?->toDateTimeString(),
            'error_message'  => $this->error_message,

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