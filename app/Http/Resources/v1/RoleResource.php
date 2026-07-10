<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'is_active'   => (bool) $this->is_active,
            'guard_name'  => $this->guard_name,

            // Counts
            'users_count'       => $this->users_count ?? $this->users()->count(),
            'permissions_count' => $this->permissions_count ?? $this->permissions()->count(),

            // Permissions list (only id + name)
            'permissions' => $this->whenLoaded(
                'permissions',
                fn () => $this->permissions->map(fn ($p) => [
                    'id'   => $p->id,
                    'name' => $p->name,
                ])
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}