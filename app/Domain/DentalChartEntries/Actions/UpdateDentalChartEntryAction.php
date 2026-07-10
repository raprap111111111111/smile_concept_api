<?php

namespace App\Domain\DentalChartEntries\Actions;

use App\Domain\DentalChartEntries\DTOs\UpdateDentalChartEntryDTO;
use App\Domain\DentalChartEntries\Repositories\DentalChartEntryRepository;
use App\Domain\DentalChartEntries\Services\DentalChartEntryService;
use App\Models\DentalChartEntry;

class UpdateDentalChartEntryAction
{
    public function __construct(
        private readonly DentalChartEntryRepository $repository,
        private readonly DentalChartEntryService $service
    ) {}

    public function execute(DentalChartEntry $entry, UpdateDentalChartEntryDTO $dto)
    {
        $dentalChartId = $dto->dentalChartId ?? $entry->dental_chart_id;
        $toothNumber = $dto->toothNumber ?? $entry->tooth_number;

        if ($dto->toothNumber !== null) {
            $this->service->validateToothNumber($toothNumber);
        }

        if ($this->repository->hasDuplicateTooth($dentalChartId, $toothNumber, $entry->id)) {
            throw new \Exception("Tooth #{$toothNumber} is already registered on this dental chart session.");
        }

        $data = array_filter([
            'dental_chart_id' => $dto->dentalChartId,
            'tooth_number' => $dto->toothNumber,
            'tooth_condition_id' => $dto->toothConditionId,
            'treatment_applied' => $dto->treatmentApplied,
        ], fn($value) => !is_null($value));

        return $this->repository->update($entry, $data);
    }
}
