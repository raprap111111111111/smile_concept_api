<?php

namespace App\Http\Controllers\v1;

use App\Domain\Prescriptions\Actions\CreatePrescriptionAction;
use App\Domain\Prescriptions\Actions\DeletePrescriptionAction;
use App\Domain\Prescriptions\Actions\UpdatePrescriptionAction;
use App\Domain\Prescriptions\Mappers\PrescriptionMapper;
use App\Domain\Prescriptions\Repositories\PrescriptionRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Prescription\DeletePrescriptionRequest;
use App\Http\Requests\v1\Prescription\GetAllPrescriptionsRequest;
use App\Http\Requests\v1\Prescription\GetPrescriptionRequest;
use App\Http\Requests\v1\Prescription\StorePrescriptionRequest;
use App\Http\Requests\v1\Prescription\UpdatePrescriptionRequest;
use App\Http\Resources\v1\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;

class PrescriptionController extends Controller
{
    public function __construct(
        private readonly PrescriptionRepository $repository,
        private readonly CreatePrescriptionAction $createAction,
        private readonly UpdatePrescriptionAction $updateAction,
        private readonly DeletePrescriptionAction $deleteAction
    ) {}

    public function index(GetAllPrescriptionsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), PrescriptionResource::class);
        return $this->successResponse($result, 'Patient medical prescriptions index retrieved.');
    }

    public function show(GetPrescriptionRequest $request, Prescription $prescription): JsonResponse
    {
        return $this->successResponse(
            new PrescriptionResource($prescription->load(['items', 'patient', 'doctor.user'])),
            'Prescription record details retrieved successfully.'
        );
    }

    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        try {
            $prescription = $this->createAction->execute(
                PrescriptionMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new PrescriptionResource($prescription),
                'Patient medical prescription logged successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdatePrescriptionRequest $request, Prescription $prescription): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $prescription,
                PrescriptionMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new PrescriptionResource($updated),
                'Patient medical prescription details updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeletePrescriptionRequest $request, Prescription $prescription): JsonResponse
    {
        $this->deleteAction->execute($prescription);
        return $this->successResponse(null, 'Patient medical prescription profile deleted.');
    }
}
