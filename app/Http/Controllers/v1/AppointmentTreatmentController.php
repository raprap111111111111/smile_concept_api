<?php

namespace App\Http\Controllers\v1;

use App\Domain\AppointmentTreatments\Actions\CreateAppointmentTreatmentAction;
use App\Domain\AppointmentTreatments\Actions\DeleteAppointmentTreatmentAction;
use App\Domain\AppointmentTreatments\Actions\UpdateAppointmentTreatmentAction;
use App\Domain\AppointmentTreatments\Mappers\AppointmentTreatmentMapper;
use App\Domain\AppointmentTreatments\Repositories\AppointmentTreatmentRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\AppointmentTreatment\DeleteAppointmentTreatmentRequest;
use App\Http\Requests\v1\AppointmentTreatment\GetAllAppointmentTreatmentsRequest;
use App\Http\Requests\v1\AppointmentTreatment\GetAppointmentTreatmentRequest;
use App\Http\Requests\v1\AppointmentTreatment\StoreAppointmentTreatmentRequest;
use App\Http\Requests\v1\AppointmentTreatment\UpdateAppointmentTreatmentRequest;
use App\Http\Resources\v1\AppointmentTreatmentResource;
use App\Models\AppointmentTreatment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AppointmentTreatmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AppointmentTreatmentRepository   $repository,
        private readonly CreateAppointmentTreatmentAction $createAction,
        private readonly UpdateAppointmentTreatmentAction $updateAction,
        private readonly DeleteAppointmentTreatmentAction $deleteAction,
    ) {}

    public function index(GetAllAppointmentTreatmentsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), AppointmentTreatmentResource::class);
        return $this->successResponse($result, 'Appointment treatments retrieved successfully.');
    }

    public function show(GetAppointmentTreatmentRequest $request, AppointmentTreatment $appointmentTreatment): JsonResponse
    {
        return $this->successResponse(
            new AppointmentTreatmentResource($appointmentTreatment->load(['appointment', 'treatment'])),
            'Appointment treatment details retrieved.'
        );
    }

    public function store(StoreAppointmentTreatmentRequest $request): JsonResponse
    {
        try {
            $item = $this->createAction->execute(
                AppointmentTreatmentMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new AppointmentTreatmentResource($item->load(['appointment', 'treatment'])),
                'Treatment added to appointment successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateAppointmentTreatmentRequest $request, AppointmentTreatment $appointmentTreatment): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $appointmentTreatment,
                AppointmentTreatmentMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new AppointmentTreatmentResource($updated->load(['appointment', 'treatment'])),
                'Appointment treatment updated successfully.'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteAppointmentTreatmentRequest $request, AppointmentTreatment $appointmentTreatment): JsonResponse
    {
        try {
            $this->deleteAction->execute($appointmentTreatment);
            return $this->successResponse(null, 'Appointment treatment removed successfully.');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}