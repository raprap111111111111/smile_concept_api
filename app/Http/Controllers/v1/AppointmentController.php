<?php

namespace App\Http\Controllers\v1;

use App\Domain\Appointments\Actions\CreateAppointmentAction;
use App\Domain\Appointments\Actions\DeleteAppointmentAction;
use App\Domain\Appointments\Actions\UpdateAppointmentAction;
use App\Domain\Appointments\Actions\UpdateAppointmentStatusAction;
use App\Domain\Appointments\Mappers\AppointmentMapper;
use App\Domain\Appointments\Repositories\AppointmentRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Appointment\DeleteAppointmentRequest;
use App\Http\Requests\v1\Appointment\GetAllAppointmentRequest;
use App\Http\Requests\v1\Appointment\GetAppointmentRequest;
use App\Http\Requests\v1\Appointment\StoreAppointmentRequest;
use App\Http\Requests\v1\Appointment\UpdateAppointmentRequest;
use App\Http\Requests\v1\Appointment\UpdateAppointmentStatusRequest;
use App\Http\Resources\v1\AppointmentResource;
use App\Models\Appointment;
use App\Http\Requests\v1\Appointment\GetAvailableSlotsRequest;
use App\Domain\Appointments\Actions\GetAppointmentCalendarCountsAction;
use App\Http\Requests\v1\Appointment\CalendarCountsAppointmentRequest;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentRepository $repository,
        private readonly CreateAppointmentAction $createAction,
        private readonly UpdateAppointmentAction $updateAction,
        private readonly DeleteAppointmentAction $deleteAction,
        private readonly UpdateAppointmentStatusAction $updateStatusAction,
    ) {}

    public function index(GetAllAppointmentRequest $request): JsonResponse
    {
        // ✅ NOW passing canViewAny and userId correctly
        $result = $this->repository->paginate(
            params: $request->validated(),
            resourceClass: AppointmentResource::class,
            canViewAny: $request->canViewAny(),   // ← Admin = true, Patient = false
            userId: $request->user()->id,           // ← Always pass current user ID
        );

        return $this->responseSuccess($result, 'Appointments retrieved successfully');
    }

    public function show(GetAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        return $this->responseSuccess(
            new AppointmentResource(
                $appointment->load(['user', 'doctor.user', 'branch', 'creator', 'invoice'])
            ),
            'Appointment found successfully'
        );
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->createAction->execute(
            AppointmentMapper::fromCreateRequest($request)
        );
        $appointment->load(['user', 'doctor.user', 'branch', 'creator']);

        return $this->responseSuccess(
            new AppointmentResource($appointment),
            'Appointment created successfully',
            JsonResponse::HTTP_CREATED
        );
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $updatedAppointment = $this->updateAction->execute(
            $appointment,
            AppointmentMapper::fromUpdateRequest($request)
        );

        return $this->responseSuccess(
            new AppointmentResource($updatedAppointment),
            'Appointment updated successfully'
        );
    }

    public function destroy(DeleteAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->deleteAction->execute($appointment);

        return $this->responseSuccess(null, 'Appointment deleted successfully.');
    }

    public function updateStatus(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment
    ): JsonResponse {
        $updated = $this->updateStatusAction->execute(
            $appointment,
            $request->status,
            $request->cancellation_reason
        );

        return $this->responseSuccess(
            new AppointmentResource(
                $updated->load(['user', 'doctor.user', 'branch', 'creator'])
            ),
            'Appointment status updated successfully'
        );
    }

    public function calendarCounts(
        CalendarCountsAppointmentRequest $request,
        GetAppointmentCalendarCountsAction $action
    ): JsonResponse {
        // ✅ Pass permission info to action
        $counts = $action->execute(
            AppointmentMapper::fromCalendarCountsRequest($request),
            canViewAny: $request->canViewAny(),
            authUserId: $request->user()->id,
        );

        return $this->successResponse(
            $counts,
            'Appointment calendar counts retrieved successfully.'
        );
    }
    public function availableSlots(GetAvailableSlotsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $slots = $this->repository->getAvailableSlots(
            doctorId: (int) $validated['doctor_id'],
            branchId: (int) $validated['branch_id'],
            date: $validated['date'],
        );

        return $this->responseSuccess($slots, 'Available slots retrieved successfully');
    }
}
