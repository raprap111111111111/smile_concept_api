<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'action'       => $this->action,
            'subject_type' => class_basename($this->subject_type),
            'subject_id'   => $this->subject_id,
            'properties'   => $this->properties,
            'ip_address'   => $this->ip_address,
            'user_agent'   => $this->user_agent,
            'url'          => $this->url,

            'user' => $this->whenLoaded('user', fn() => [
                'id'    => $this->user?->id,
                'name'  => $this->user?->name,
                'email' => $this->user?->email,
            ]),

            'created_at' => $this->created_at,
        ];
    }
}