<?php

namespace App\Http\Controllers\v1;

use App\Domain\ToothConditions\Actions\CreateToothConditionAction;
use App\Domain\ToothConditions\Actions\DeleteToothConditionAction;
use App\Domain\ToothConditions\Actions\UpdateToothConditionAction;
use App\Domain\ToothConditions\Mappers\ToothConditionMapper;
use App\Domain\ToothConditions\Repositories\ToothConditionRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\ToothCondition\GetAllToothConditionRequest;
use App\Http\Requests\v1\ToothCondition\StoreToothConditionRequest;
use App\Http\Requests\v1\ToothCondition\UpdateToothConditionRequest;
use App\Http\Resources\v1\ToothConditionResource;
use App\Models\ToothCondition;
use Illuminate\Http\JsonResponse;

class ToothConditionController extends Controller
{
    public function __construct(
        private readonly ToothConditionRepository $repository,
        private readonly CreateToothConditionAction $createAction,
        private readonly UpdateToothConditionAction $updateAction,
        private readonly DeleteToothConditionAction $deleteAction
    ) {}

    public function index(GetAllToothConditionRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), ToothConditionResource::class);
        return $this->responseSuccess($result, 'Tooth conditions list retrieved.');
    }

    public function show(ToothCondition $toothCondition): JsonResponse
    {
        return $this->responseSuccess(
            new ToothConditionResource($toothCondition),
            'Tooth condition details fetched.'
        );
    }

    public function store(StoreToothConditionRequest $request): JsonResponse
    {
        $condition = $this->createAction->execute(
            ToothConditionMapper::fromCreateRequest($request)
        );

        return $this->responseSuccess(
            new ToothConditionResource($condition),
            'Tooth condition created successfully.',
            JsonResponse::HTTP_CREATED
        );
    }

    public function update(UpdateToothConditionRequest $request, ToothCondition $toothCondition): JsonResponse
    {
        $updated = $this->updateAction->execute(
            $toothCondition,
            ToothConditionMapper::fromUpdateRequest($request)
        );

        return $this->responseSuccess(
            new ToothConditionResource($updated),
            'Tooth condition updated successfully.'
        );
    }

    public function destroy(ToothCondition $toothCondition): JsonResponse
    {
        try {
            $this->deleteAction->execute($toothCondition);
            return $this->responseSuccess(null, 'Tooth condition deleted successfully.');
        } catch (\Exception $e) {
            return $this->responseError('Cannot delete a tooth condition that is currently assigned to a patient chart entry.', 409);
        }
    }
}
