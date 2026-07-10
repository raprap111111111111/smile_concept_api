<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'branch_code'   => $this->branch_code,
            'address'       => $this->address,
            'city'          => $this->city,
            'province'      => $this->province,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'is_active'     => (bool) $this->is_active,
            'opening_hours' => $this->opening_hours,

            // Counts (only present when loaded via withCount)
            'staff_count'        => $this->staff_count ?? 0,
            'appointments_count' => $this->appointments_count ?? 0,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}