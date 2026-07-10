<?php

namespace App\Domain\Branch\Mappers;

use App\Domain\Branch\DTOs\CreateBranchDTO;
use App\Domain\Branch\DTOs\UpdateBranchDTO;
use App\Http\Requests\v1\Branch\StoreBranchRequest;
use App\Http\Requests\v1\Branch\UpdateBranchRequest;

class BranchMapper
{
    /* --------------------------------------------
     | Request → DTO (CREATE)
     |-------------------------------------------- */
    public static function fromCreateRequest(StoreBranchRequest $request): CreateBranchDTO
    {
        return new CreateBranchDTO(
            name: $request->validated('name'),
            branchCode: $request->validated('branch_code'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            province: $request->validated('province'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            isActive: (bool) $request->validated('is_active', true),
            openingHours: $request->validated('opening_hours'),
        );
    }

    /* --------------------------------------------
     | Request → DTO (UPDATE)
     |-------------------------------------------- */
    public static function fromUpdateRequest(UpdateBranchRequest $request): UpdateBranchDTO
    {
        return new UpdateBranchDTO(
            name: $request->validated('name'),
            branchCode: $request->validated('branch_code'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            province: $request->validated('province'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            isActive: (bool) $request->validated('is_active', true),
            openingHours: $request->validated('opening_hours'),
        );
    }

    /* --------------------------------------------
     | Helpers
     |-------------------------------------------- */
    private static function optionalString(object $request, string $key): ?string
    {
        return $request->filled($key)
            ? trim((string) $request->input($key))
            : null;
    }
}
