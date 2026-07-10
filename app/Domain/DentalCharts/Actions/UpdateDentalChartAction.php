<?php

namespace App\Domain\DentalCharts\Actions;

use App\Domain\DentalCharts\DTOs\UpdateDentalChartDTO;
use App\Domain\DentalCharts\Repositories\DentalChartRepository;
use App\Domain\DentalCharts\Services\DentalChartService;
use App\Models\DentalChart;
use Illuminate\Support\Facades\DB;

class UpdateDentalChartAction
{
    public function __construct(
        private readonly DentalChartRepository $repository,
        private readonly DentalChartService $service
    ) {}

    public function execute(DentalChart $dentalChart, UpdateDentalChartDTO $dto)
    {
        if ($dto->entries !== null) {
            foreach ($dto->entries as $entry) {
                $this->service->validateToothNumber($entry->toothNumber);
            }
        }

        return DB::transaction(function () use ($dentalChart, $dto) {
            $parentData = array_filter([
                'user_id' => $dto->userId,
                'appointment_id' => $dto->appointmentId,
                'general_notes' => $dto->generalNotes,
            ], fn($value) => !is_null($value));

            $this->repository->update($dentalChart, $parentData);

            if ($dto->entries !== null) {
                $dentalChart->entries()->delete();

                foreach ($dto->entries as $entry) {
                    $dentalChart->entries()->create([
                        'tooth_number' => $entry->toothNumber,
                        'tooth_condition_id' => $entry->condition,
                        'treatment_applied' => $entry->treatmentApplied,
                    ]);
                }
            }

            return $dentalChart->load(['entries.condition']);
        });
    }
}
