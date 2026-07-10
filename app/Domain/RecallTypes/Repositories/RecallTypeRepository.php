<?php

namespace App\Domain\RecallTypes\Repositories;

use App\Models\RecallType;
use App\Support\Query\BaseRepository;

class RecallTypeRepository extends BaseRepository
{
    protected string $model = RecallType::class;

    protected array $searchable = [
        'slug',
        'label',
    ];

    protected array $filterable = [
        'is_active',
    ];

    protected array $sortable = [
        'id',
        'slug',
        'label',
        'frequency_months',
        'created_at',
    ];

    protected string $defaultOrderBy = 'id';
    protected string $defaultOrderDirection = 'asc';
}
