<?php

namespace App\Domain\Payments\Repositories;

use App\Models\Payment;
use App\Support\Query\BaseRepository;

class PaymentRepository extends BaseRepository
{
    protected string $model = Payment::class;

    // ✅ Prevents N+1
    protected array $with = ['invoice'];

    protected array $searchable = [
        'transaction_reference',
        'notes',
    ];

    protected array $filterable = [
        'invoice_id',
        'payment_method',
    ];

    protected array $sortable = [
        'id',
        'amount',
        'payment_date',
        'created_at',
    ];

    protected string $defaultOrderBy        = 'payment_date';
    protected string $defaultOrderDirection = 'desc';
}