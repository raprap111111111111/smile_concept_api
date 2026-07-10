<?php

namespace App\Domain\TreatmentPlans\Actions;

use App\Domain\TreatmentPlans\Repositories\TreatmentPlanRepository;
use App\Models\TreatmentPlan;

class DeleteTreatmentPlanAction
{
    public function __construct(
        private readonly TreatmentPlanRepository $repository
    ) {}

    public function execute(TreatmentPlan $plan): bool
    {
        return $this->repository->delete($plan);
    }
}
