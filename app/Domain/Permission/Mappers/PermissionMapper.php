<?php

namespace App\Domain\Permission\Mappers;

use App\Domain\Permission\DTOs\CreatePermissionDTO;
use App\Domain\Permission\DTOs\UpdatePermissionDTO;
use App\Http\Requests\v1\Permission\StorePermissionRequest;
use App\Http\Requests\v1\Permission\UpdatePermissionRequest;

class PermissionMapper
{
    public static function fromCreateRequest(StorePermissionRequest $request): CreatePermissionDTO
    {
        return new CreatePermissionDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    public static function fromUpdateRequest(UpdatePermissionRequest $request): UpdatePermissionDTO
    {
        return new UpdatePermissionDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            isActive: $request->validated('is_active'),
        );
    }
}