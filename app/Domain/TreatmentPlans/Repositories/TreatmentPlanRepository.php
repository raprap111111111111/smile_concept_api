<?php

namespace App\Domain\TreatmentPlans\Repositories;

use App\Enums\StockMovementType;
use App\Models\TreatmentPlan;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TreatmentPlanRepository extends BaseRepository
{
    protected string $model = TreatmentPlan::class;

    // TreatmentPlanResource reads every one of these. Without eager loading the
    // index endpoint lazy-loads them once per plan.
    protected array $relations = ['items.treatment', 'patient', 'doctor.user'];

    public function query(): Builder
    {
        // consumables_recorded rides along as a correlated subquery so the
        // resource can say whether supplies were already deducted for a plan
        // without an extra query per row.
        return parent::query()->withExists([
            'stockMovements as consumables_recorded' => fn (Builder $q) => $q->where('type', StockMovementType::CONSUMPTION),
        ]);
    }
    protected array $searchable = ['name'];
    protected array $filterable = ['user_id', 'doctor_id', 'status'];
    protected array $sortable = ['id', 'total_estimated_amount', 'created_at'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && ! $user->can('treatment-plan.viewAny')) {
            $params['user_id'] = $user->id;
            unset($params['doctor_id']);
        }

        return parent::paginate($params, $resourceClass);
    }

    /**
     * Retrieve all treatment plans
     */
    public function all()
    {
        return ($this->model)::with(['items.treatment', 'patient', 'doctor.user'])->get();
    }
}
