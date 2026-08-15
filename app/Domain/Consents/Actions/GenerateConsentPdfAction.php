<?php

namespace App\Domain\Consents\Actions;

use App\Models\PatientConsent;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfInstance;

class GenerateConsentPdfAction
{
    public function execute(PatientConsent $consent): PdfInstance
    {
        $consent->load([
            'template',
            'patient.patientProfile',
            'appointment.doctor.user',
            'signedByStaff',
        ]);

        $patient = $consent->patient;

        if (! $patient) {
            throw new \DomainException(
                "Consent [{$consent->id}] has no patient (user_id is null or invalid). " .
                    "Fix the sign pipeline — user_id must be the patient."
            );
        }

        $formData = $consent->form_data ?? [];
        $formPatient = $formData['patient_info'] ?? [];
        $formMedical = $formData['medical'] ?? [];
        $formIntraoral = $formData['intraoral']['selections'] ?? [];

        $bladePatient = (object) array_merge(
            (array) $patient,
            [
                'name' => $formPatient['name'] ?? $patient->name ?? '',
                'birthdate' => $formPatient['birthdate'] ?? ($patient->birthdate ?? ''),
                'religion' => $formPatient['religion'] ?? ($patient->religion ?? ''),
                'address' => $formPatient['home_address'] ?? ($patient->address ?? ''),
                'occupation' => $formPatient['occupation'] ?? ($patient->occupation ?? ''),
                'dental_insurance' => $formPatient['dental_insurance'] ?? ($patient->dental_insurance ?? ''),
                'effective_date' => $formPatient['effective_date'] ?? ($patient->effective_date ?? ''),
                'nickname' => $formPatient['nickname'] ?? ($patient->nickname ?? ''),
                'email' => $formPatient['email'] ?? ($patient->email ?? ''),
                'phone' => $formPatient['phone'] ?? ($patient->phone ?? ''),
                'referred_by' => $formPatient['referred_by'] ?? ($patient->referred_by ?? ''),
                'consultation_reason' => $formPatient['consultation_reason'] ?? ($patient->consultation_reason ?? ''),
                'previous_dentist' => $formPatient['previous_dentist'] ?? ($patient->previous_dentist ?? ''),
                'last_dental_visit' => $formPatient['last_dental_visit'] ?? ($patient->last_dental_visit ?? ''),
                'guardian_name' => ($formData['guardian_profile']['guardian_name'] ?? $formPatient['guardian_name'] ?? ($patient->guardian_name ?? '')),
                'guardian_occupation' => ($formData['guardian_profile']['guardian_occupation'] ?? $formPatient['guardian_occupation'] ?? ($patient->guardian_occupation ?? '')),
            ]
        );

        $bladeMedical = (object) $formMedical;
        $bladeDentalChart = $formIntraoral;

        return Pdf::loadView('pdf.consent', [
            'consent'         => $consent,
            'template'        => $consent->template,
            'patient'         => $bladePatient,
            'medicalProfile'  => $bladeMedical,
            'dentalChart'     => $bladeDentalChart,
            'treatments'      => $this->getTreatmentHistory($patient->id ?? $consent->user_id),
            'signature'       => $consent->signature_data ?? '',
            'clinic'          => config('app.name'),
            'pdaLogo'         => $this->getLogoBase64(),
        ])->setPaper('legal', 'landscape');
    }

    private function getLogoBase64(): ?string
    {
        try {
            $path = public_path('images/pda-logo.png');
            if (! file_exists($path)) {
                return null;
            }
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getDentalChartData(int $userId): array
    {
        try {
            $chart = \App\Models\DentalChart::where('user_id', $userId)
                ->latest()
                ->first();
            if (! $chart) {
                return [];
            }
            return collect($chart->teeth ?? [])
                ->pluck('condition_code', 'tooth_number')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTreatmentHistory(int $userId): \Illuminate\Support\Collection
    {
        try {
            return \App\Models\Invoice::where('user_id', $userId)
                ->with('items.treatment')
                ->latest()
                ->limit(30)
                ->get()
                ->flatMap(function ($invoice) {
                    return $invoice->items->map(fn($item) => (object) [
                        'date'           => $invoice->created_at->format('M d, Y'),
                        'tooth_no'       => $item->tooth_number ?? '',
                        'procedure'      => $item->treatment?->name ?? $item->description ?? '',
                        'amount_charged' => $item->amount ?? 0,
                        'amount_paid'    => $invoice->amount_paid ?? 0,
                        'balance'        => $invoice->balance ?? 0,
                    ]);
                });
        } catch (\Exception $e) {
            return collect();
        }
    }
}
