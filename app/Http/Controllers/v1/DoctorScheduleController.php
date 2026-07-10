<?php

namespace App\Http\Controllers\v1;

use App\Domain\DoctorSchedules\Actions\CreateDoctorScheduleAction;
use App\Domain\DoctorSchedules\Actions\DeleteDoctorScheduleAction;
use App\Domain\DoctorSchedules\Actions\UpdateDoctorScheduleAction;
use App\Domain\DoctorSchedules\Mappers\DoctorScheduleMapper;
use App\Domain\DoctorSchedules\Repositories\DoctorScheduleRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\DoctorSchedule\DeleteDoctorScheduleRequest;
use App\Http\Requests\v1\DoctorSchedule\GetAllDoctorScheduleRequest;
use App\Http\Requests\v1\DoctorSchedule\GetDoctorScheduleRequest;
use App\Http\Requests\v1\DoctorSchedule\StoreDoctorScheduleRequest;
use App\Http\Requests\v1\DoctorSchedule\UpdateDoctorScheduleRequest;
use App\Http\Resources\v1\DoctorScheduleResource;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;

class DoctorScheduleController extends Controller
{
    public function __construct(
        private readonly DoctorScheduleRepository $repository,
        private readonly CreateDoctorScheduleAction $createAction,
        private readonly UpdateDoctorScheduleAction $updateAction,
        private readonly DeleteDoctorScheduleAction $deleteAction
    ) {}

    public function index(GetAllDoctorScheduleRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), DoctorScheduleResource::class);
        return $this->successResponse($result, 'Doctor schedules list retrieved successfully.');
    }

    public function show(GetDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule): JsonResponse
    {
        return $this->successResponse(
            new DoctorScheduleResource($doctorSchedule->load(['doctor.user', 'branch'])),
            'Doctor schedule entry loaded successfully.'
        );
    }

    public function store(StoreDoctorScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->createAction->execute(
                DoctorScheduleMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new DoctorScheduleResource($schedule->load(['doctor.user', 'branch'])),
                'Doctor schedule created successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $doctorSchedule,
                DoctorScheduleMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new DoctorScheduleResource($updated->load(['doctor.user', 'branch'])),
                'Doctor schedule updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule): JsonResponse
    {
        $this->deleteAction->execute($doctorSchedule);
        return $this->successResponse(null, 'Doctor schedule deleted successfully.');
    }
}
