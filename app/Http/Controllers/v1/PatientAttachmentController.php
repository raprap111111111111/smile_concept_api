<?php

namespace App\Http\Controllers\v1;

use App\Domain\PatientAttachments\Actions\CreatePatientAttachmentAction;
use App\Domain\PatientAttachments\Actions\DeletePatientAttachmentAction;
use App\Domain\PatientAttachments\Actions\UpdatePatientAttachmentAction;
use App\Domain\PatientAttachments\Mappers\PatientAttachmentMapper;
use App\Domain\PatientAttachments\Repositories\PatientAttachmentRepository;
use App\Http\Controllers\Concerns\ServesFiles;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\PatientAttachment\DeletePatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\GetAllPatientAttachmentsRequest;
use App\Http\Requests\v1\PatientAttachment\GetPatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\StorePatientAttachmentRequest;
use App\Http\Requests\v1\PatientAttachment\UpdatePatientAttachmentRequest;
use App\Http\Resources\v1\PatientAttachmentResource;
use App\Models\PatientAttachment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientAttachmentController extends Controller
{
    use ServesFiles;

    /** Storage disk (matches config/filesystems.php) */
    private const DISK = 'public';

    /** Subfolder inside the disk */
    private const FOLDER = 'patient-attachments';

    public function __construct(
        private readonly PatientAttachmentRepository $repository,
        private readonly CreatePatientAttachmentAction $createAction,
        private readonly UpdatePatientAttachmentAction $updateAction,
        private readonly DeletePatientAttachmentAction $deleteAction
    ) {}

    // ═══════════════════════════════════════════════════════
    // CRUD
    // ═══════════════════════════════════════════════════════

    public function index(GetAllPatientAttachmentsRequest $request): JsonResponse
    {
        $result = $this->repository->paginate(
            $request->validated(),
            PatientAttachmentResource::class
        );

        return $this->successResponse($result, 'Patient attachments retrieved.');
    }

    public function show(GetPatientAttachmentRequest $request, PatientAttachment $patientAttachment): JsonResponse
    {
        return $this->successResponse(
            new PatientAttachmentResource($patientAttachment->load('patient')),
            'Attachment fetched successfully.'
        );
    }

    public function store(StorePatientAttachmentRequest $request): JsonResponse
    {
        try {
            $uploadedPath = $request->file('file')->store(self::FOLDER, self::DISK);

            $dto = PatientAttachmentMapper::fromCreateRequest($request, $uploadedPath);
            $attachment = $this->createAction->execute($dto);

            return $this->successResponse(
                new PatientAttachmentResource($attachment),
                'Attachment uploaded successfully.',
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
                'Attachment updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(DeletePatientAttachmentRequest $request, PatientAttachment $patientAttachment): JsonResponse
    {
        $this->deleteAction->execute($patientAttachment);
        return $this->successResponse(null, 'Attachment removed.');
    }

    // ═══════════════════════════════════════════════════════
    // FOLDER VIEWS
    // ═══════════════════════════════════════════════════════

    public function patients(Request $request): JsonResponse
    {
        $search  = $request->query('search');
        $perPage = min((int) $request->query('per_page', 20), 100);

        $patients = User::query()
            ->select(['id', 'name', 'email', 'profile_photo'])
            ->withCount([
                'patientAttachments as attachment_count',
                'patientAttachments as xray_count' => fn($q) => $q->where('is_xray', true),
                'patientAttachments as pending_scans' => fn($q) =>
                $q->whereIn('scan_status', ['pending', 'processing']),
            ])
            ->having('attachment_count', '>', 0)
            ->when($search, fn($q, $s) => $q->where(
                fn($sub) =>
                $sub->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
            ))
            ->orderByDesc('attachment_count')
            ->paginate($perPage);

        return $this->successResponse([
            'records'      => $patients->items(),
            'total'        => $patients->total(),
            'current_page' => $patients->currentPage(),
            'last_page'    => $patients->lastPage(),
            'per_page'     => $patients->perPage(),
            'has_more'     => $patients->hasMorePages(),
        ], 'Patients with attachments retrieved.');
    }

    public function byPatient(Request $request, int $userId): JsonResponse
    {
        $patient = User::select('id', 'name', 'email', 'profile_photo')->findOrFail($userId);
        $filters = array_merge($request->query(), ['user_id' => $userId]);

        $result = $this->repository->paginate($filters, PatientAttachmentResource::class);

        return $this->successResponse([
            ...$result,
            'patient' => $patient,
        ], "Attachments for {$patient->name} retrieved.");
    }

    // ═══════════════════════════════════════════════════════
    // ✅ FILE STREAMING (Authenticated)
    // ═══════════════════════════════════════════════════════

    /**
     * Stream file inline (for viewing images/PDFs).
     * GET /api/v1/patient-attachments/{id}/file
     */
    public function file(PatientAttachment $patientAttachment)
    {
        // Optional: Add authorization
        // $this->authorize('view', $patientAttachment);

        return $this->streamFile(
            relativePath: $this->getPath($patientAttachment),
            disk: self::DISK,
        );
    }

    /**
     * Force download.
     * GET /api/v1/patient-attachments/{id}/download
     */
    public function download(PatientAttachment $patientAttachment)
    {
        // $this->authorize('download', $patientAttachment);

        return $this->downloadFile(
            relativePath: $this->getPath($patientAttachment),
            disk: self::DISK,
            downloadName: $this->buildDownloadName($patientAttachment),
        );
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    private function getPath(PatientAttachment $attachment): ?string
    {
        return $attachment->file_path ?? $attachment->path ?? null;
    }

    private function buildDownloadName(PatientAttachment $attachment): string
    {
        $original  = basename($this->getPath($attachment) ?? 'file');
        $extension = pathinfo($original, PATHINFO_EXTENSION) ?: 'bin';
        $slug      = str($attachment->title ?? 'attachment')->slug();

        return "{$slug}-{$attachment->id}.{$extension}";
    }
}
