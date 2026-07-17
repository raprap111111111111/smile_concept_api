<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\ClinicalNote;
use App\Models\DentalChart;
use App\Models\LabCase;
use App\Models\PatientAttachment;
use App\Models\Prescription;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicalRecordsController extends Controller
{
    use ApiResponse;

    public function summary(Request $request): JsonResponse
    {
        $dentalChartQuery = DentalChart::query();

        $clinicalNoteQuery = ClinicalNote::query();

        $labCaseQuery = LabCase::query();

        $attachmentQuery = PatientAttachment::query();

        $prescriptionQuery = Prescription::query();

        $summary = [
            'counts' => [
                'dental_charts'   => $dentalChartQuery->count(),
                'clinical_notes'  => $clinicalNoteQuery->count(),
                'lab_cases'       => $labCaseQuery->count(),
                'attachments'     => $attachmentQuery->count(),
                'prescriptions'   => $prescriptionQuery->count(),
            ],

            'lab_case_stats' => [
                'pending'  => LabCase::query()
                    ->whereIn('status', ['sent', 'pending'])
                    ->count(),
                'received' => LabCase::query()
                    ->where('status', '=', 'received')
                    ->count(),
                'overdue'  => LabCase::query()
                    ->where('due_date', '<', now())
                    ->whereNotIn('status', ['received', 'installed'])
                    ->count(),
            ],

            'recent_notes' => ClinicalNote::query()
                ->with(['doctor.user', 'appointment.user'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($note) => [
                    'id'              => $note->id,
                    'appointment_id'  => $note->appointment_id,
                    'doctor_name'     => $note->doctor?->user?->name,
                    'patient_name'    => $note->appointment?->user?->name,
                    'treatment_notes' => str($note->treatment_notes)->limit(100),
                    'is_locked'       => (bool) $note->is_locked,
                    'created_at'      => $note->created_at,
                ]),

            'recent_attachments' => PatientAttachment::query()
                ->with('patient')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($attachment) => [
                    'id'           => $attachment->id,
                    'file_name'    => $attachment->file_name,
                    'file_type'    => $attachment->file_type,
                    'patient_name' => $attachment->patient?->name,
                    'created_at'   => $attachment->created_at,
                ]),
        ];

        return $this->responseSuccess($summary, 'Clinical records summary retrieved.');
    }

    public function patientSummary(int $patientId): JsonResponse
    {
        /** @var User $patient */
        $patient = User::query()
            ->with('patientProfile')
            ->findOrFail($patientId);

        $summary = [
            'patient' => [
                'id'            => $patient->id,
                'name'          => $patient->name,
                'email'         => $patient->email,
                'phone'         => $patient->phone,
                'profile_photo' => $patient->profile_photo,
            ],

            'medical_alerts' => [
                'requires_epinephrine_free_anesthesia' => $patient->patientProfile?->requires_epinephrine_free_anesthesia ?? false,
                'has_cardiac_conditions'               => $patient->patientProfile?->has_cardiac_conditions ?? false,
                'is_pregnant'                          => $patient->patientProfile?->is_pregnant ?? false,
                'has_bleeding_disorders'               => $patient->patientProfile?->has_bleeding_disorders ?? false,
                'allergies'                            => $patient->patientProfile?->allergies,
                'medical_history'                      => $patient->patientProfile?->medical_history,
                'current_medications'                  => $patient->patientProfile?->current_medications,
                'blood_type'                           => $patient->patientProfile?->blood_type,
            ],

            'counts' => [
                'dental_charts'  => DentalChart::query()
                    ->where('user_id', '=', $patientId)
                    ->count(),

                'clinical_notes' => ClinicalNote::query()
                    ->whereHas('appointment', fn($q) => $q->where('user_id', '=', $patientId))
                    ->count(),

                'lab_cases'      => LabCase::query()
                    ->whereHas('appointment', fn($q) => $q->where('user_id', '=', $patientId))
                    ->count(),

                'attachments'    => PatientAttachment::query()
                    ->where('user_id', '=', $patientId)
                    ->count(),

                'prescriptions'  => Prescription::query()
                    ->where('user_id', '=', $patientId)
                    ->count(),
            ],

            'recent_activity' => [
                'notes' => ClinicalNote::query()
                    ->whereHas('appointment', fn($q) => $q->where('user_id', '=', $patientId))
                    ->with('doctor.user')
                    ->latest()
                    ->limit(5)
                    ->get(),

                'attachments' => PatientAttachment::query()
                    ->where('user_id', '=', $patientId)
                    ->latest()
                    ->limit(5)
                    ->get(),

                'prescriptions' => Prescription::query()
                    ->where('user_id', '=', $patientId)
                    ->with('doctor.user')
                    ->latest()
                    ->limit(5)
                    ->get(),
            ],
        ];

        return $this->responseSuccess($summary, 'Patient clinical summary retrieved.');
    }
}
