<?php

namespace App\Domain\ToothConditions\Repositories;

use App\Models\ToothCondition;
use App\Support\Query\BaseRepository;

class ToothConditionRepository extends BaseRepository
{
    protected string $model = ToothCondition::class;

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
        'created_at',
    ];

    protected string $defaultOrderBy = 'id';
    protected string $defaultOrderDirection = 'asc';
}
