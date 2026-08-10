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

        // Hard guard: consent must have a valid patient
        if (! $patient) {
            throw new \DomainException(
                "Consent [{$consent->id}] has no patient (user_id is null or invalid). " .
                "Fix the sign pipeline — user_id must be the patient."
            );
        }

        return Pdf::loadView('pdf.consent', [
            'consent'        => $consent,
            'template'       => $consent->template,
            'patient'        => $patient,                        // always the patient ✓
            'medicalProfile' => $patient->patientProfile ?? null,
            'dentalChart'    => $this->getDentalChartData($patient->id),
            'treatments'     => $this->getTreatmentHistory($patient->id),
            'signature'      => $consent->signature_data ?? '',
            'clinic'         => config('app.name'),
        ])->setPaper('a4', 'landscape');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────────────────────────────

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
                    return $invoice->items->map(fn ($item) => (object) [
                        'date'           => $invoice->created_at->format('M d, Y'),
                        'tooth_no'       => $item->tooth_number ?? '',
                        'procedure'      => $item->treatment?->name
                                               ?? $item->description
                                               ?? '',
                        'amount_charged' => $item->amount       ?? 0,
                        'amount_paid'    => $invoice->amount_paid ?? 0,
                        'balance'        => $invoice->balance    ?? 0,
                    ]);
                });

        } catch (\Exception $e) {
            return collect();
        }
    }
}