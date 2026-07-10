<?php

namespace App\Http\Controllers\v1;

use App\Domain\PatientAttachments\Actions\CreatePatientAttachmentAction;
use App\Domain\PatientAttachments\Actions\DeletePatientAttachmentAction;
use App\Domain\PatientAttachments\Actions\UpdatePatientAttachmentAction;
use App\Domain\PatientAttachments\Mappers\PatientAttachmentMapper;
use App\Domain\PatientAttachments\Repositories\PatientAttachmentRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\PatientAttachment\DeletePatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\GetAllPatientAttachmentsRequest;
use App\Http\Requests\v1\PatientAttachment\GetPatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\StorePatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\UpdatePatientAttachmentRequest;
use App\Http\Resources\v1\PatientAttachmentResource;
use App\Models\PatientAttachment;
use Illuminate\Http\JsonResponse;

class PatientAttachmentController extends Controller
{
    public function __construct(
        private readonly PatientAttachmentRepository $repository,
        private readonly CreatePatientAttachmentAction $createAction,
        private readonly UpdatePatientAttachmentAction $updateAction,
        private readonly DeletePatientAttachmentAction $deleteAction
    ) {}

    public function index(GetAllPatientAttachmentsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), PatientAttachmentResource::class);
        return $this->successResponse($result, 'Patient diagnostics attachments list retrieved.');
    }

    public function show(GetPatientAttachmentRequest $request, PatientAttachment $patientAttachment): JsonResponse
    {
        return $this->successResponse(
            new PatientAttachmentResource($patientAttachment->load('patient')),
            'Attachment profile fetched successfully.'
        );
    }

    public function store(StorePatientAttachmentRequest $request): JsonResponse
    {
        try {
            $attachment = $this->createAction->execute(
                PatientAttachmentMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new PatientAttachmentResource($attachment),
                'Patient diagnostic media stored successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdatePatientAttachmentRequest $request, PatientAttachment $patientAttachment): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $patientAttachment,
                PatientAttachmentMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new PatientAttachmentResource($updated),
                'Diagnostic media record updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeletePatientAttachmentRequest $request, PatientAttachment $patientAttachment): JsonResponse
    {
        $this->deleteAction->execute($patientAttachment);
        return $this->successResponse(null, 'Patient diagnostic media removed.');
    }
}
