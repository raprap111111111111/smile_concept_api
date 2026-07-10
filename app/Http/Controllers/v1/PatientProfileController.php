<?php

namespace App\Http\Controllers\v1;

use App\Domain\PatientProfiles\Actions\CreatePatientProfileAction;
use App\Domain\PatientProfiles\Actions\DeletePatientProfileAction;
use App\Domain\PatientProfiles\Actions\UpdatePatientProfileAction;
use App\Domain\PatientProfiles\Mappers\PatientProfileMapper;
use App\Domain\PatientProfiles\Repositories\PatientProfileRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\PatientProfile\DeletePatientProfileRequest;
use App\Http\Requests\v1\PatientProfile\GetAllPatientProfilesRequest;
use App\Http\Requests\v1\PatientProfile\GetPatientProfileRequest;
use App\Http\Requests\v1\PatientProfile\StorePatientProfileRequest;
use App\Http\Requests\v1\PatientProfile\UpdatePatientProfileRequest;
use App\Http\Resources\v1\PatientProfileResource;
use App\Models\PatientProfile;
use App\Traits\ApiResponse;                       // ✅ added
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientProfileController extends Controller
{
    use ApiResponse;                              // ✅ added

    public function __construct(
        private readonly PatientProfileRepository   $repository,
        private readonly CreatePatientProfileAction $createAction,
        private readonly UpdatePatientProfileAction $updateAction,
        private readonly DeletePatientProfileAction $deleteAction,
    ) {}

    public function index(GetAllPatientProfilesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), PatientProfileResource::class);
        return $this->successResponse($result, 'Patient medical profiles retrieved.');
    }

    public function show(GetPatientProfileRequest $request, PatientProfile $patientProfile): JsonResponse
    {
        return $this->successResponse(
            new PatientProfileResource($patientProfile->load('user')),
            'Patient medical profile details retrieved.'
        );
    }

    /**
     * Return the current user's medical profile.
     */
    public function me(): JsonResponse
    {
        $user    = Auth::user();
        $profile = PatientProfile::with('user')->where('user_id', $user->id)->first();

        if (!$profile) {
            return $this->errorResponse('No patient profile found for the current user.', 404);
        }

        return $this->successResponse(
            new PatientProfileResource($profile),
            'Current patient profile retrieved.'
        );
    }

    public function store(StorePatientProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->createAction->execute(
                PatientProfileMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new PatientProfileResource($profile->load('user')),
                'Patient created successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdatePatientProfileRequest $request, PatientProfile $patientProfile): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $patientProfile,
                PatientProfileMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new PatientProfileResource($updated->load('user')),
                'Patient medical profile updated successfully.'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeletePatientProfileRequest $request, PatientProfile $patientProfile): JsonResponse
    {
        try {
            $this->deleteAction->execute($patientProfile);
            return $this->successResponse(null, 'Patient medical profile successfully deleted.');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}