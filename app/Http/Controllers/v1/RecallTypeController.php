<?php

namespace App\Http\Controllers\v1;

use App\Domain\RecallTypes\Actions\CreateRecallTypeAction;
use App\Domain\RecallTypes\Actions\DeleteRecallTypeAction;
use App\Domain\RecallTypes\Actions\UpdateRecallTypeAction;
use App\Domain\RecallTypes\Mappers\RecallTypeMapper;
use App\Domain\RecallTypes\Repositories\RecallTypeRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\RecallType\DeleteRecallTypeRequest;
use App\Http\Requests\v1\RecallType\GetAllRecallTypesRequest;
use App\Http\Requests\v1\RecallType\GetRecallTypeRequest;
use App\Http\Requests\v1\RecallType\StoreRecallTypeRequest;
use App\Http\Requests\v1\RecallType\UpdateRecallTypeRequest;
use App\Http\Resources\v1\RecallTypeResource;
use App\Models\RecallType;
use Illuminate\Http\JsonResponse;

class RecallTypeController extends Controller
{
    public function __construct(
        private readonly RecallTypeRepository $repository,
        private readonly CreateRecallTypeAction $createAction,
        private readonly UpdateRecallTypeAction $updateAction,
        private readonly DeleteRecallTypeAction $deleteAction
    ) {}

    public function index(GetAllRecallTypesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), RecallTypeResource::class);
        return $this->successResponse($result, 'Recall types list retrieved.');
    }

    public function show(GetRecallTypeRequest $request, RecallType $recallType): JsonResponse
    {
        return $this->successResponse(
            new RecallTypeResource($recallType),
            'Recall type configuration details fetched.'
        );
    }

    public function store(StoreRecallTypeRequest $request): JsonResponse
    {
        try {
            $type = $this->createAction->execute(
                RecallTypeMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new RecallTypeResource($type),
                'Recall type configuration registered successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateRecallTypeRequest $request, RecallType $recallType): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $recallType,
                RecallTypeMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new RecallTypeResource($updated),
                'Recall type configuration updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteRecallTypeRequest $request, RecallType $recallType): JsonResponse
    {
        try {
            $this->deleteAction->execute($recallType);
            return $this->successResponse(null, 'Recall type configuration deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Cannot delete a recall type configuration that is assigned to active patient records.', 409);
        }
    }
}
