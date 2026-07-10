<?php

namespace App\Domain\DentalCharts\Actions;

use App\Domain\DentalCharts\DTOs\CreateDentalChartDTO;
use App\Domain\DentalCharts\Repositories\DentalChartRepository;
use App\Domain\DentalCharts\Services\DentalChartService;
use Illuminate\Support\Facades\DB;

class CreateDentalChartAction
{
    public function __construct(
        private readonly DentalChartRepository $repository,
        private readonly DentalChartService $service
    ) {}

    public function execute(CreateDentalChartDTO $dto)
    {
        foreach ($dto->entries as $entry) {
            $this->service->validateToothNumber($entry->toothNumber);
        }

        return DB::transaction(function () use ($dto) {
            $chart = $this->repository->create([
                'user_id' => $dto->userId,
                'appointment_id' => $dto->appointmentId,
                'general_notes' => $dto->generalNotes,
            ]);

            foreach ($dto->entries as $entry) {
                $chart->entries()->create([
                    'tooth_number' => $entry->toothNumber,
                    'tooth_condition_id' => $entry->condition, // Write relation foreign key
                    'treatment_applied' => $entry->treatmentApplied,
                ]);
            }

            return $chart->load(['entries.condition']);
        });
    }
}
