<?php

namespace App\Domain\Treatments\Repositories;

use App\Models\Treatment;
use App\Support\Query\BaseRepository;

class TreatmentRepository extends BaseRepository
{
    protected string $model = Treatment::class;

    protected array $searchable = [
        'name',
        'description',
    ];

    protected array $filterable = [
        'is_active',
    ];

    protected array $sortable = [
        'id',
        'name',
        'price',
        'created_at',
    ];

    protected string $defaultOrderBy = 'name';
    protected string $defaultOrderDirection = 'asc';
}
