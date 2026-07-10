<?php

namespace App\Domain\DentalChartEntries\Mappers;

use App\Domain\DentalChartEntries\DTOs\CreateDentalChartEntryDTO;
use App\Domain\DentalChartEntries\DTOs\UpdateDentalChartEntryDTO;
use App\Http\Requests\v1\DentalChartEntry\StoreDentalChartEntryRequest;
use App\Http\Requests\v1\DentalChartEntry\UpdateDentalChartEntryRequest;

class DentalChartEntryMapper
{
    public static function fromCreateRequest(StoreDentalChartEntryRequest $request): CreateDentalChartEntryDTO
    {
        return new CreateDentalChartEntryDTO(
            dentalChartId: (int) $request->validated('dental_chart_id'),
            toothNumber: $request->validated('tooth_number'),
            toothConditionId: (int) $request->validated('tooth_condition_id'),
            treatmentApplied: $request->validated('treatment_applied')
        );
    }

    public static function fromUpdateRequest(UpdateDentalChartEntryRequest $request): UpdateDentalChartEntryDTO
    {
        return new UpdateDentalChartEntryDTO(
            dentalChartId: $request->validated('dental_chart_id') ? (int) $request->validated('dental_chart_id') : null,
            toothNumber: $request->validated('tooth_number'),
            toothConditionId: $request->validated('tooth_condition_id') ? (int) $request->validated('tooth_condition_id') : null,
            treatmentApplied: $request->validated('treatment_applied')
        );
    }
}
