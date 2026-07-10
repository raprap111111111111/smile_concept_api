<?php

namespace App\Domain\AppointmentTreatments\Repositories;

use App\Models\AppointmentTreatment;
use App\Support\Query\BaseRepository;

class AppointmentTreatmentRepository extends BaseRepository
{
    protected string $model = AppointmentTreatment::class;

    protected array $with = ['appointment', 'treatment'];

    protected array $searchable = [
        'tooth_number',
        'notes',
    ];

    protected array $filterable = [
        'appointment_id',
        'treatment_id',
        'tooth_number',
    ];

    protected array $sortable = [
        'id',
        'price_charged',
        'created_at',
    ];

    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}