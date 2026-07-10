<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AppointmentResource;
use App\Http\Resources\v1\InvoiceResource;
use App\Http\Resources\v1\TreatmentPlanResource;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientPortalController extends Controller
{
    /**
     * Aggregated profile dashboard for self-service portal
     */
    public function dashboard(Request $request): JsonResponse
    {
        // Get the currently authenticated patient from the API Guard
        $patient = $request->user();

        // 1. Retrieve future bookings securely using ::query()
        $appointments = Appointment::query()
            ->where('user_id', $patient->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->with(['doctor.user', 'branch'])
            ->get();

        // 2. Retrieve past billing history securely using ::query()
        $invoices = Invoice::query()
            ->whereHas('appointment', function ($q) use ($patient) {
                $q->where('user_id', $patient->id);
            })
            ->with(['items.treatment', 'payments'])
            ->get();

        // 3. Retrieve planned clinical steps securely using ::query()
        $treatmentPlans = TreatmentPlan::query()
            ->where('user_id', $patient->id)
            ->with(['items.treatment', 'doctor.user'])
            ->get();

        // 4. Return aggregated and formatted resources to client
        return $this->successResponse([
            'patient_profile' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->phone,
            ],
            'upcoming_appointments' => AppointmentResource::collection($appointments),
            'treatment_plans' => TreatmentPlanResource::collection($treatmentPlans),
            'billing_history' => InvoiceResource::collection($invoices),
        ], 'Patient self-service profile dashboard compiled.');
    }
}
