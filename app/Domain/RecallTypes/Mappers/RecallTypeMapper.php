<?php

namespace App\Domain\RecallTypes\Mappers;

use App\Domain\RecallTypes\DTOs\CreateRecallTypeDTO;
use App\Domain\RecallTypes\DTOs\UpdateRecallTypeDTO;
use App\Http\Requests\v1\RecallType\StoreRecallTypeRequest;
use App\Http\Requests\v1\RecallType\UpdateRecallTypeRequest;
use Illuminate\Support\Str;

class RecallTypeMapper
{
    public static function fromCreateRequest(StoreRecallTypeRequest $request): CreateRecallTypeDTO
    {
        $label = $request->validated('label');
        return new CreateRecallTypeDTO(
            slug: $request->validated('slug') ?? Str::slug($label),
            label: $label,
            frequencyMonths: (int) $request->validated('frequency_months', 6),
            isActive: (bool) $request->validated('is_active', true)
        );
    }

    public static function fromUpdateRequest(UpdateRecallTypeRequest $request): UpdateRecallTypeDTO
    {
        return new UpdateRecallTypeDTO(
            slug: $request->validated('slug'),
            label: $request->validated('label'),
            frequencyMonths: $request->has('frequency_months') ? (int) $request->validated('frequency_months') : null,
            isActive: $request->has('is_active') ? (bool) $request->validated('is_active') : null
        );
    }
}
