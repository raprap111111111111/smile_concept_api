<?php

namespace App\Http\Controllers\v1;

use App\Domain\Treatments\Actions\CreateTreatmentAction;
use App\Domain\Treatments\Actions\DeleteTreatmentAction;
use App\Domain\Treatments\Actions\UpdateTreatmentAction;
use App\Domain\Treatments\Mappers\TreatmentMapper;
use App\Domain\Treatments\Repositories\TreatmentRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Treatment\DeleteTreatmentRequest;
use App\Http\Requests\v1\Treatment\GetAllTreatmentsRequest;
use App\Http\Requests\v1\Treatment\GetTreatmentRequest;
use App\Http\Requests\v1\Treatment\StoreTreatmentRequest;
use App\Http\Requests\v1\Treatment\UpdateTreatmentRequest;
use App\Http\Resources\v1\TreatmentResource;
use App\Models\Treatment;
use Illuminate\Http\JsonResponse;

class TreatmentController extends Controller
{
    public function __construct(
        private readonly TreatmentRepository $repository,
        private readonly CreateTreatmentAction $createAction,
        private readonly UpdateTreatmentAction $updateAction,
        private readonly DeleteTreatmentAction $deleteAction
    ) {}

    public function index(GetAllTreatmentsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), TreatmentResource::class);
        return $this->successResponse($result, 'Treatments catalog retrieved.');
    }

    public function show(GetTreatmentRequest $request, Treatment $treatment): JsonResponse
    {
        return $this->successResponse(
            new TreatmentResource($treatment),
            'Treatment catalog entry retrieved.'
        );
    }

    public function store(StoreTreatmentRequest $request): JsonResponse
    {
        try {
            $treatment = $this->createAction->execute(
                TreatmentMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new TreatmentResource($treatment),
                'Treatment created successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateTreatmentRequest $request, Treatment $treatment): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $treatment,
                TreatmentMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new TreatmentResource($updated),
                'Treatment catalog entry updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteTreatmentRequest $request, Treatment $treatment): JsonResponse
    {
        try {
            $this->deleteAction->execute($treatment);
            return $this->successResponse(null, 'Treatment catalog entry successfully archived/deleted.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 409);
        }
    }
}
