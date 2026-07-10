<?php

namespace App\Domain\DentalChartEntries\Actions;

use App\Domain\DentalChartEntries\DTOs\CreateDentalChartEntryDTO;
use App\Domain\DentalChartEntries\Repositories\DentalChartEntryRepository;
use App\Domain\DentalChartEntries\Services\DentalChartEntryService;

class CreateDentalChartEntryAction
{
    public function __construct(
        private readonly DentalChartEntryRepository $repository,
        private readonly DentalChartEntryService $service
    ) {}

    public function execute(CreateDentalChartEntryDTO $dto)
    {
        $this->service->validateToothNumber($dto->toothNumber);

        if ($this->repository->hasDuplicateTooth($dto->dentalChartId, $dto->toothNumber)) {
            throw new \Exception("Tooth #{$dto->toothNumber} is already registered on this dental chart session.");
        }

        return $this->repository->create([
            'dental_chart_id' => $dto->dentalChartId,
            'tooth_number' => $dto->toothNumber,
            'tooth_condition_id' => $dto->toothConditionId,
            'treatment_applied' => $dto->treatmentApplied,
        ]);
    }
}
