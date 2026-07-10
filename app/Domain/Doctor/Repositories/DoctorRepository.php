<?php

namespace App\Domain\Doctor\Repositories;

use App\Models\Doctor;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class DoctorRepository extends BaseRepository
{
    protected string $model = Doctor::class;

    protected array $searchable = ['specialization', 'license_number'];
    protected array $filterable = ['specialization'];
    protected array $sortable   = ['id', 'created_at'];

    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    public function query(): Builder
    {
        return parent::query()
            ->with(['user.branches'])
            ->withCount(['schedules', 'appointments']);
    }
}