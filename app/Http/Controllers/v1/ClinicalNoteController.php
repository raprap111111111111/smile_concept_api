<?php

namespace App\Http\Controllers\v1;

use App\Domain\ClinicalNotes\Actions\CreateClinicalNoteAction;
use App\Domain\ClinicalNotes\Actions\DeleteClinicalNoteAction;
use App\Domain\ClinicalNotes\Actions\UpdateClinicalNoteAction;
use App\Domain\ClinicalNotes\Mappers\ClinicalNoteMapper;
use App\Domain\ClinicalNotes\Repositories\ClinicalNoteRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\ClinicalNote\DeleteClinicalNoteRequest;
use App\Http\Requests\v1\ClinicalNote\GetAllClinicalNotesRequest;
use App\Http\Requests\v1\ClinicalNote\GetClinicalNoteRequest;
use App\Http\Requests\v1\ClinicalNote\StoreClinicalNoteRequest;
use App\Http\Requests\v1\ClinicalNote\UpdateClinicalNoteRequest;
use App\Http\Resources\v1\ClinicalNoteResource;
use App\Models\ClinicalNote;
use Illuminate\Http\JsonResponse;

class ClinicalNoteController extends Controller
{
    public function __construct(
        private readonly ClinicalNoteRepository $repository,
        private readonly CreateClinicalNoteAction $createAction,
        private readonly UpdateClinicalNoteAction $updateAction,
        private readonly DeleteClinicalNoteAction $deleteAction
    ) {}

    public function index(GetAllClinicalNotesRequest $request): JsonResponse
    {
        $result = $this->repository->paginate($request->validated(), ClinicalNoteResource::class);
        return $this->successResponse($result, 'Clinical session running progress notes retrieved.');
    }

    public function show(GetClinicalNoteRequest $request, ClinicalNote $clinicalNote): JsonResponse
    {
        return $this->successResponse(
            new ClinicalNoteResource($clinicalNote->load('doctor.user')),
            'Progress session notes retrieved successfully.'
        );
    }

    public function store(StoreClinicalNoteRequest $request): JsonResponse
    {
        try {
            $note = $this->createAction->execute(
                ClinicalNoteMapper::fromCreateRequest($request)
            );

            return $this->successResponse(
                new ClinicalNoteResource($note),
                'Progress session notes logged successfully.',
                JsonResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(UpdateClinicalNoteRequest $request, ClinicalNote $clinicalNote): JsonResponse
    {
        try {
            $updated = $this->updateAction->execute(
                $clinicalNote,
                ClinicalNoteMapper::fromUpdateRequest($request)
            );

            return $this->successResponse(
                new ClinicalNoteResource($updated),
                'Progress session notes modified successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeleteClinicalNoteRequest $request, ClinicalNote $clinicalNote): JsonResponse
    {
        try {
            $this->deleteAction->execute($clinicalNote);
            return $this->successResponse(null, 'Progress session notes discarded successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
