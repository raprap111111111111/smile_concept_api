<?php

namespace App\Domain\DentalChartEntries\Repositories;

use App\Models\DentalChartEntry;
use App\Support\Query\BaseRepository;

class DentalChartEntryRepository extends BaseRepository
{
    protected string $model = DentalChartEntry::class;

    protected array $searchable = [
        'tooth_number',
        'treatment_applied',
    ];

    protected array $filterable = [
        'dental_chart_id',
        'tooth_condition_id',
    ];

    protected array $sortable = [
        'id',
        'tooth_number',
        'created_at',
    ];

    protected string $defaultOrderBy = 'id';
    protected string $defaultOrderDirection = 'asc';

    /**
     * Check if a specific tooth condition record is already charted on this chart session
     */
    public function hasDuplicateTooth(int $dentalChartId, string $toothNumber, ?int $excludeId = null): bool
    {
        $query = ($this->model)::query()
            ->where('dental_chart_id', $dentalChartId)
            ->where('tooth_number', $toothNumber);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
