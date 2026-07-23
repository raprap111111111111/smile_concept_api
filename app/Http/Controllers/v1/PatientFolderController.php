<?php

namespace App\Http\Controllers\v1;

use App\Domain\PatientAttachments\Repositories\PatientAttachmentRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\PatientAttachment\GetPatientFolderRequest;
use App\Http\Resources\v1\PatientAttachmentResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /patient-folders          → List all patient folders
 * GET /patient-folders/{userId} → Files inside a specific patient folder
 */
class PatientFolderController extends Controller
{
    public function __construct(
        private readonly PatientAttachmentRepository $repository
    ) {}

    // ═══════════════════════════════════════════════════════
    // 📁 LIST FOLDERS (all patients with attachments)
    // ═══════════════════════════════════════════════════════

    public function index(Request $request): JsonResponse
    {
        $search  = $request->query('search');
        $perPage = min((int) $request->query('per_page', 20), 100);

        $patients = User::query()
            ->select(['id', 'name', 'email', 'profile_photo'])
            ->role('patient')
            ->withCount([
                // ✅ NO uploaded_by filter — staff uploads FOR patients
                // ALL attachments belong to the patient via user_id
                'patientAttachments as attachment_count',

                'patientAttachments as xray_count' => fn($q)
                => $q->where('is_xray', true),

                'patientAttachments as pending_scans' => fn($q)
                => $q->whereIn('scan_status', ['pending', 'processing']),
            ])
            ->having('attachment_count', '>', 0)
            ->when(
                $search,
                fn($q) => $q->where(
                    fn($sub) => $sub
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->orderByDesc('attachment_count')
            ->paginate($perPage);

        return $this->successResponse([
            'records'      => $patients->items(),
            'total'        => $patients->total(),
            'current_page' => $patients->currentPage(),
            'last_page'    => $patients->lastPage(),
            'per_page'     => $patients->perPage(),
            'has_more'     => $patients->hasMorePages(),
        ], 'Patient folders retrieved.');
    }

    // ═══════════════════════════════════════════════════════
    // 📂 SHOW FOLDER (files for specific patient)
    // ═══════════════════════════════════════════════════════

    public function show(GetPatientFolderRequest $request, int $userId): JsonResponse
{
    $patient = User::role('patient')
        ->select('id', 'name', 'email', 'profile_photo')
        ->findOrFail($userId);

    // ✅ FIX ATTEMPT — pass query directly instead of through paginate()
    $query = \App\Models\PatientAttachment::query()
        ->where('user_id', $userId);  // ← force filter here

    $filters = $request->validated();
    
    $result = $this->repository->paginateQuery(
        $query,
        $filters,
        PatientAttachmentResource::class
    );

    return $this->successResponse([
        ...$result,
        'patient' => $patient,
    ], "Folder contents for {$patient->name} retrieved.");
}
}
