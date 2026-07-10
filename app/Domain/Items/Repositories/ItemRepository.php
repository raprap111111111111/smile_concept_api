<?php

namespace App\Domain\Items\Repositories;

use App\Models\Item;
use App\Support\Query\BaseRepository;

class ItemRepository extends BaseRepository
{
    protected string $model = Item::class;

    protected array $searchable = [
        'name',
        'sku',
        'category',
    ];

    protected array $filterable = [
        'category',
        'unit_of_measure',
    ];

    protected array $sortable = [
        'id',
        'name',
        'sku',
        'minimum_threshold',
        'created_at',
    ];

    protected string $defaultOrderBy = 'name';
    protected string $defaultOrderDirection = 'asc';
}
