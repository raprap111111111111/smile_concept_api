<?php

namespace App\Domain\DentalCharts\Repositories;

use App\Models\DentalChart;
use App\Support\Query\BaseRepository;

class DentalChartRepository extends BaseRepository
{
    protected string $model = DentalChart::class;

    protected array $searchable = [
        'general_notes',
    ];

    protected array $filterable = [
        'user_id',
        'appointment_id',
    ];

    protected array $sortable = [
        'id',
        'created_at',
    ];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}
