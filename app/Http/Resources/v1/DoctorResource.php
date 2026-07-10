<?php
// app/Http/Resources/v1/DoctorResource.php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,

            // ─── Professional Info ────────────────────
            'license_number'      => $this->license_number,
            'specialization'      => $this->specialization,
            'bio'                 => $this->bio,
            'consultation_fee'    => $this->consultation_fee !== null
                                        ? (float) $this->consultation_fee
                                        : null,
            'years_of_experience' => (int) $this->years_of_experience,
            'signature_path'      => $this->signature_path,
            'signature_url'       => $this->signature_path
                                        ? asset('storage/' . $this->signature_path)
                                        : null,
            'is_active'           => (bool) $this->is_active,

            // ─── User (nested) ────────────────────────
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id'            => $this->user->id,
                    'name'          => $this->user->name,
                    'email'         => $this->user->email,
                    'phone'         => $this->user->phone,
                    'profile_photo' => $this->user->profile_photo
                        ? asset('storage/' . $this->user->profile_photo)
                        : null,
                    'is_active'     => $this->user->email_verified_at !== null,
                    'branches'      => $this->user->branches?->map(fn($b) => [
                        'id'   => $b->id,
                        'name' => $b->name,
                    ]) ?? [],
                ];
            }),

            // ─── Counts (when eager loaded) ───────────
            'schedules_count'     => $this->whenCounted('schedules'),
            'appointments_count'  => $this->whenCounted('appointments'),

            // ─── Timestamps ───────────────────────────
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}