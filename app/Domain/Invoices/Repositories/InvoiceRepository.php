<?php

namespace App\Domain\Invoices\Repositories;

use App\Models\Invoice;
use App\Support\Query\BaseRepository;

class InvoiceRepository extends BaseRepository
{
    protected string $model = Invoice::class;

    // ✅ Eager load to prevent N+1 on index listing
    protected array $with = [
        'items.treatment',
        'payments',
    ];

    protected array $searchable = [
        'invoice_number', // ✅ Added: useful for search
        'status',
    ];

    protected array $filterable = [
        'appointment_id',
        'status',
    ];

    protected array $sortable = [
        'id',
        'total_amount',
        'balance_due',
        'due_date',     // ✅ Added: overdue tracking
        'created_at',
    ];

    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    
}