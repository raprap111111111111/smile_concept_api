<?php

namespace App\Domain\Role\Mappers;

use App\Domain\Role\DTOs\CreateRoleDTO;
use App\Domain\Role\DTOs\UpdateRoleDTO;
use App\Http\Requests\v1\Role\StoreRoleRequest;
use App\Http\Requests\v1\Role\UpdateRoleRequest;

class RoleMapper
{
    public static function fromCreateRequest(StoreRoleRequest $request): CreateRoleDTO
    {
        return new CreateRoleDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    public static function fromUpdateRequest(UpdateRoleRequest $request): UpdateRoleDTO
    {
        $validated = $request->validated();

        return new UpdateRoleDTO(
            name: $validated['name'] ?? null,
            description: array_key_exists('description', $validated) ? $validated['description'] : null,
            isActive: array_key_exists('is_active', $validated) ? (bool)$validated['is_active'] : null,
        );
    }
}
