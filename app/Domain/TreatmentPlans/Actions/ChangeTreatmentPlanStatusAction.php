<?php

namespace App\Domain\TreatmentPlans\Actions;

use App\Domain\TreatmentPlans\DTOs\ChangeTreatmentPlanStatusDTO;
use App\Domain\TreatmentPlans\Exceptions\InvalidStatusTransitionException;
use App\Enums\TreatmentPlanStatus;
use App\Models\TreatmentPlan;
use Illuminate\Support\Facades\DB;

class ChangeTreatmentPlanStatusAction
{
    public function execute(
        TreatmentPlan $plan,
        ChangeTreatmentPlanStatusDTO $dto
    ): TreatmentPlan {
        $current = $plan->status instanceof TreatmentPlanStatus
            ? $plan->status
            : TreatmentPlanStatus::from($plan->status);

        // No-op if same status
        if ($current === $dto->status) {
            return $plan;
        }

        // Enforce state machine
        if (! $current->canTransitionTo($dto->status)) {
            throw InvalidStatusTransitionException::from($current, $dto->status);
        }

        DB::transaction(function () use ($plan, $dto, $current) {
            $plan->update(['status' => $dto->status->value]);

            // Log to history table if the relation exists
            if (method_exists($plan, 'statusHistory')) {
                $plan->statusHistory()->create([
                    'from_status' => $current->value,
                    'to_status'   => $dto->status->value,
                    'reason'      => $dto->reason,
                    'changed_by'  => $dto->changedBy ?? auth()->id(),
                ]);
            }
        });

        return $plan->fresh(['items.treatment', 'patient', 'doctor.user']);
    }
}