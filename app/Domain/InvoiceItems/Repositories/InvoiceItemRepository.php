<?php

namespace App\Domain\InvoiceItems\Repositories;

use App\Models\InvoiceItem;
use App\Support\Query\BaseRepository;

class InvoiceItemRepository extends BaseRepository
{
    protected string $model = InvoiceItem::class;

    protected array $searchable = [];

    protected array $filterable = [
        'invoice_id',
        'treatment_id',
    ];

    protected array $sortable = [
        'id',
        'quantity',
        'total_price',
        'created_at',
    ];

    protected string $defaultOrderBy = 'id';
    protected string $defaultOrderDirection = 'asc';
}
