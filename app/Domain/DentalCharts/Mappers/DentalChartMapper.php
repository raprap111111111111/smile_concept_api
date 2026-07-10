<?php

namespace App\Domain\DentalCharts\Mappers;

use App\Domain\DentalCharts\DTOs\CreateDentalChartDTO;
use App\Domain\DentalCharts\DTOs\CreateDentalChartEntryDTO;
use App\Domain\DentalCharts\DTOs\UpdateDentalChartDTO;
use App\Http\Requests\v1\DentalChart\StoreDentalChartRequest;
use App\Http\Requests\v1\DentalChart\UpdateDentalChartRequest;

class DentalChartMapper
{
    public static function fromCreateRequest(StoreDentalChartRequest $request): CreateDentalChartDTO
    {
        $entries = array_map(function ($entry) {
            return new CreateDentalChartEntryDTO(
                toothNumber: $entry['tooth_number'],
                condition: $entry['tooth_condition_id'], // Map ID directly now
                treatmentApplied: $entry['treatment_applied'] ?? null
            );
        }, $request->validated('entries', []));

        return new CreateDentalChartDTO(
            userId: (int) $request->validated('user_id'),
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            generalNotes: $request->validated('general_notes'),
            entries: $entries
        );
    }

    public static function fromUpdateRequest(UpdateDentalChartRequest $request): UpdateDentalChartDTO
    {
        $entries = null;
        if ($request->has('entries')) {
            $entries = array_map(function ($entry) {
                return new CreateDentalChartEntryDTO(
                    toothNumber: $entry['tooth_number'],
                    condition: $entry['tooth_condition_id'],
                    treatmentApplied: $entry['treatment_applied'] ?? null
                );
            }, $request->validated('entries', []));
        }

        return new UpdateDentalChartDTO(
            userId: $request->validated('user_id') ? (int) $request->validated('user_id') : null,
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            generalNotes: $request->validated('general_notes'),
            entries: $entries
        );
    }
}
