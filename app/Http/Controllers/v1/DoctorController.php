<?php
// app/Http/Controllers/v1/DoctorController.php

namespace App\Http\Controllers\v1;

use App\Domain\Doctor\Actions\CreateDoctorAction;
use App\Domain\Doctor\Actions\DeleteDoctorAction;
use App\Domain\Doctor\Actions\UpdateDoctorAction;
use App\Domain\Doctor\Mappers\DoctorMapper;
use App\Domain\Doctor\Repositories\DoctorRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Doctor\GetAllDoctorRequest;
use App\Http\Requests\v1\Doctor\StoreDoctorRequest;
use App\Http\Requests\v1\Doctor\UpdateDoctorRequest;
use App\Http\Resources\v1\DoctorResource;
use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DoctorRepository $repository,
        private readonly CreateDoctorAction $createAction,
        private readonly UpdateDoctorAction $updateAction,
        private readonly DeleteDoctorAction $deleteAction,
    ) {}

    public function index(GetAllDoctorRequest $request): JsonResponse
    {
        $result = $this->repository->paginate(
            $request->validated(),
            DoctorResource::class
        );

        return $this->successResponse($result, 'Doctors retrieved successfully');
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return $this->successResponse(
            new DoctorResource(
                $doctor->load(['user.branches', 'schedules', 'appointments'])
            ),
            'Doctor retrieved successfully'
        );
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        try {
            $doctor = $this->createAction->execute(
                DoctorMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new DoctorResource($doctor),
                'Doctor created successfully',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $doctor,
                DoctorMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new DoctorResource($updated),
                'Doctor updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(Doctor $doctor): JsonResponse
    {
        try {
            $this->deleteAction->execute($doctor);
            return $this->successResponse(null, 'Doctor deleted successfully');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}