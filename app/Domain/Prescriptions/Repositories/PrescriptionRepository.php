<?php

namespace App\Domain\Prescriptions\Repositories;

use App\Models\Prescription;
use App\Support\Query\BaseRepository;

class PrescriptionRepository extends BaseRepository
{
    protected string $model = Prescription::class;

    protected array $searchable = [
        'notes',
    ];

    protected array $filterable = [
        'appointment_id',
        'doctor_id',
        'user_id',
    ];

    protected array $sortable = [
        'id',
        'created_at',
    ];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}
