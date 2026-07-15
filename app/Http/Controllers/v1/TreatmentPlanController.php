<?php

namespace App\Http\Controllers\v1;

use App\Domain\TreatmentPlans\Actions\ChangeTreatmentPlanStatusAction;
use App\Domain\TreatmentPlans\Actions\CreateTreatmentPlanAction;
use App\Domain\TreatmentPlans\Actions\DeleteTreatmentPlanAction;
use App\Domain\TreatmentPlans\Actions\UpdateTreatmentPlanAction;
use App\Domain\TreatmentPlans\Exceptions\InvalidStatusTransitionException;
use App\Domain\TreatmentPlans\Mappers\TreatmentPlanMapper;
use App\Domain\TreatmentPlans\Repositories\TreatmentPlanRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\TreatmentPlan\ChangeTreatmentPlanStatusRequest;
use App\Http\Requests\v1\TreatmentPlan\DeleteTreatmentPlanRequest;
use App\Http\Requests\v1\TreatmentPlan\GetAllTreatmentPlansRequest;
use App\Http\Requests\v1\TreatmentPlan\GetTreatmentPlanRequest;
use App\Http\Requests\v1\TreatmentPlan\StoreTreatmentPlanRequest;
use App\Http\Requests\v1\TreatmentPlan\UpdateTreatmentPlanRequest;
use App\Http\Resources\v1\TreatmentPlanResource;
use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;

class TreatmentPlanController extends Controller
{
    public function __construct(
        private readonly TreatmentPlanRepository $repository,
        private readonly CreateTreatmentPlanAction $createAction,
        private readonly UpdateTreatmentPlanAction $updateAction,
        private readonly DeleteTreatmentPlanAction $deleteAction,
        private readonly ChangeTreatmentPlanStatusAction $changeStatusAction,
    ) {}

    public function index(GetAllTreatmentPlansRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), TreatmentPlanResource::class);
        return $this->successResponse($result, 'Dental treatment plans estimate log retrieved.');
    }

    public function show(GetTreatmentPlanRequest $request, TreatmentPlan $treatmentPlan): JsonResponse
    {
        return $this->successResponse(
            new TreatmentPlanResource($treatmentPlan->load(['items.treatment', 'patient', 'doctor.user'])),
            'Treatment plan clinical profile retrieved.'
        );
    }

    public function store(StoreTreatmentPlanRequest $request): JsonResponse
    {
        try {
            $plan = $this->createAction->execute(
                TreatmentPlanMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new TreatmentPlanResource($plan),
                'Case quote treatment plan constructed.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateTreatmentPlanRequest $request, TreatmentPlan $treatmentPlan): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $treatmentPlan,
                TreatmentPlanMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new TreatmentPlanResource($updated),
                'Treatment plan details updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteTreatmentPlanRequest $request, TreatmentPlan $treatmentPlan): JsonResponse
    {
        $this->deleteAction->execute($treatmentPlan);
        return $this->successResponse(null, 'Treatment plan catalog estimate deleted.');
    }

    public function changeStatus(
        ChangeTreatmentPlanStatusRequest $request,  
        TreatmentPlan $treatmentPlan
    ): JsonResponse {
        try {
            $updated = $this->changeStatusAction->execute(
                $treatmentPlan,
                TreatmentPlanMapper::fromStatusChangeRequest($request)
            );

            return $this->successResponse(
                new TreatmentPlanResource($updated),
                "Plan status changed to {$updated->status->label()}."
            );
        } catch (InvalidStatusTransitionException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}