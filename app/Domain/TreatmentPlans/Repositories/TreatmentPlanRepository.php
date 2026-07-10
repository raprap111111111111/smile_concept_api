<?php

namespace App\Domain\TreatmentPlans\Repositories;

use App\Models\TreatmentPlan;
use App\Support\Query\BaseRepository;

class TreatmentPlanRepository extends BaseRepository
{
    protected string $model = TreatmentPlan::class;
    protected array $searchable = ['name'];
    protected array $filterable = ['user_id', 'doctor_id', 'status'];
    protected array $sortable = ['id', 'total_estimated_amount', 'created_at'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    /**
     * Retrieve all treatment plans
     */
    public function all()
    {
        return ($this->model)::with(['items.treatment', 'patient', 'doctor.user'])->get();
    }
}
