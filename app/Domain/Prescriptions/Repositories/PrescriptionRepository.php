<?php

namespace App\Domain\Prescriptions\Repositories;

use App\Models\Prescription;
use App\Support\Query\BaseRepository;
use Illuminate\Support\Facades\Auth;

class PrescriptionRepository extends BaseRepository
{
    protected string $model = Prescription::class;

    protected array $relations = [
        'items',
        'patient',
        'doctor.user',
    ];

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
        'user_id',
        'doctor_id',
    ];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && ! $user->can('prescription.viewAny')) {
            $params['user_id'] = $user->id;
            unset($params['doctor_id'], $params['appointment_id']);
        }

        return parent::paginate($params, $resourceClass);
    }
}