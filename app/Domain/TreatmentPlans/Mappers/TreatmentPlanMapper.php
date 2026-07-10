<?php

namespace App\Domain\TreatmentPlans\Mappers;

use App\Domain\TreatmentPlans\DTOs\CreateTreatmentPlanDTO;
use App\Domain\TreatmentPlans\DTOs\CreateTreatmentPlanItemDTO;
use App\Domain\TreatmentPlans\DTOs\UpdateTreatmentPlanDTO;
use App\Http\Requests\v1\TreatmentPlan\StoreTreatmentPlanRequest;
use App\Http\Requests\v1\TreatmentPlan\UpdateTreatmentPlanRequest;

class TreatmentPlanMapper
{
    public static function fromCreateRequest(StoreTreatmentPlanRequest $request): CreateTreatmentPlanDTO
    {
        $items = array_map(function ($item) {
            return new CreateTreatmentPlanItemDTO(
                treatmentId: (int) $item['treatment_id'],
                sequenceOrder: (int) $item['sequence_order'],
                notes: $item['notes'] ?? null
            );
        }, $request->validated('items', []));

        return new CreateTreatmentPlanDTO(
            userId: (int) $request->validated('user_id'),
            doctorId: (int) $request->validated('doctor_id'),
            name: $request->validated('name'),
            notes: $request->validated('notes'),
            items: $items
        );
    }

    public static function fromUpdateRequest(UpdateTreatmentPlanRequest $request): UpdateTreatmentPlanDTO
    {
        $items = null;
        if ($request->has('items')) {
            $items = array_map(function ($item) {
                return new CreateTreatmentPlanItemDTO(
                    treatmentId: (int) $item['treatment_id'],
                    sequenceOrder: (int) $item['sequence_order'],
                    notes: $item['notes'] ?? null
                );
            }, $request->validated('items', []));
        }

        return new UpdateTreatmentPlanDTO(
            userId: $request->validated('user_id') ? (int) $request->validated('user_id') : null,
            doctorId: $request->validated('doctor_id') ? (int) $request->validated('doctor_id') : null,
            name: $request->validated('name'),
            status: $request->validated('status'),
            notes: $request->validated('notes'),
            items: $items
        );
    }
}
