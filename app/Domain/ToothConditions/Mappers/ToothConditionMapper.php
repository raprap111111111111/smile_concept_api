<?php

namespace App\Domain\ToothConditions\Mappers;

use App\Domain\ToothConditions\DTOs\CreateToothConditionDTO;
use App\Domain\ToothConditions\DTOs\UpdateToothConditionDTO;
use App\Http\Requests\v1\ToothCondition\StoreToothConditionRequest;
use App\Http\Requests\v1\ToothCondition\UpdateToothConditionRequest;
use Illuminate\Support\Str;

class ToothConditionMapper
{
    public static function fromCreateRequest(StoreToothConditionRequest $request): CreateToothConditionDTO
    {
        $label = $request->validated('label');
        return new CreateToothConditionDTO(
            slug: $request->validated('slug') ?? Str::slug($label),
            label: $label,
            colorCode: $request->validated('color_code', '#808080'),
            isActive: (bool) $request->validated('is_active', true)
        );
    }

    public static function fromUpdateRequest(UpdateToothConditionRequest $request): UpdateToothConditionDTO
    {
        return new UpdateToothConditionDTO(
            slug: $request->validated('slug'),
            label: $request->validated('label'),
            colorCode: $request->validated('color_code'),
            isActive: $request->has('is_active') ? (bool) $request->validated('is_active') : null
        );
    }
}
