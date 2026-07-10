<?php

namespace App\Http\Controllers\v1;

use App\Domain\LabCases\Actions\CreateLabCaseAction;
use App\Domain\LabCases\Actions\DeleteLabCaseAction;
use App\Domain\LabCases\Actions\UpdateLabCaseAction;
use App\Domain\LabCases\Mappers\LabCaseMapper;
use App\Domain\LabCases\Repositories\LabCaseRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\LabCase\DeleteLabCaseRequest;
use App\Http\Requests\v1\LabCase\GetAllLabCasesRequest;
use App\Http\Requests\v1\LabCase\GetLabCaseRequest;
use App\Http\Requests\v1\LabCase\StoreLabCaseRequest;
use App\Http\Requests\v1\LabCase\UpdateLabCaseRequest;
use App\Http\Resources\v1\LabCaseResource;
use App\Models\LabCase;
use Illuminate\Http\JsonResponse;

class LabCaseController extends Controller
{
    public function __construct(
        private readonly LabCaseRepository $repository,
        private readonly CreateLabCaseAction $createAction,
        private readonly UpdateLabCaseAction $updateAction,
        private readonly DeleteLabCaseAction $deleteAction
    ) {}

    public function index(GetAllLabCasesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), LabCaseResource::class);
        return $this->successResponse($result, 'Laboratory case logs retrieved.');
    }

    public function show(GetLabCaseRequest $request, LabCase $labCase): JsonResponse
    {
        return $this->successResponse(
            new LabCaseResource($labCase),
            'Laboratory case tracking file resolved.'
        );
    }

    public function store(StoreLabCaseRequest $request): JsonResponse
    {
        try {
            $labCase = $this->createAction->execute(
                LabCaseMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new LabCaseResource($labCase),
                'Laboratory case log established.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateLabCaseRequest $request, LabCase $labCase): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $labCase,
                LabCaseMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new LabCaseResource($updated),
                'Laboratory case record modified.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteLabCaseRequest $request, LabCase $labCase): JsonResponse
    {
        $this->deleteAction->execute($labCase);
        return $this->successResponse(null, 'Laboratory case record dropped.');
    }
}
