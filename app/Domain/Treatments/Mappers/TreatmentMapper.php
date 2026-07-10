<?php

namespace App\Domain\Treatments\Mappers;

use App\Domain\Treatments\DTOs\CreateTreatmentDTO;
use App\Domain\Treatments\DTOs\UpdateTreatmentDTO;
use App\Http\Requests\v1\Treatment\StoreTreatmentRequest;
use App\Http\Requests\v1\Treatment\UpdateTreatmentRequest;

class TreatmentMapper
{
    public static function fromCreateRequest(StoreTreatmentRequest $request): CreateTreatmentDTO
    {
        return new CreateTreatmentDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (float) $request->validated('price'),
            estimatedDurationMinutes: (int) $request->validated('estimated_duration_minutes', 30),
            isActive: (bool) $request->validated('is_active', true)
        );
    }

    public static function fromUpdateRequest(UpdateTreatmentRequest $request): UpdateTreatmentDTO
    {
        return new UpdateTreatmentDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: $request->has('price') ? (float) $request->validated('price') : null,
            estimatedDurationMinutes: $request->has('estimated_duration_minutes') ? (int) $request->validated('estimated_duration_minutes') : null,
            isActive: $request->has('is_active') ? (bool) $request->validated('is_active') : null
        );
    }
}
