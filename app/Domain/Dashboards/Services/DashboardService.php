<?php

namespace App\Domain\Dashboards\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Recall;
use App\Models\Appointment;
use App\Enums\RecallStatus;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAnalyticsSummary(): array
    {
        return [
            'financials' => $this->getFinancialMetrics(),
            'treatment_popularity' => $this->getTreatmentPopularity(),
            'patient_growth' => $this->getPatientGrowth(),
            'recall_efficiency' => $this->getRecallEfficiency(),
            'doctor_utilization' => $this->getDoctorUtilization(),
        ];
    }

    private function getFinancialMetrics(): array
    {
        return [
            // Outstanding Accounts Receivable Balance Due
            'outstanding_accounts_receivable' => round((float) Invoice::where('status', '!=', 'void')->sum('balance_due'), 2),
            'total_revenue_billed' => round((float) Invoice::where('status', '!=', 'void')->sum('total_amount'), 2),
            'total_payments_collected' => round((float) DB::table('payments')->whereNull('deleted_at')->sum('amount'), 2),
        ];
    }

    private function getTreatmentPopularity(): array
    {
        // Treatments generating the most revenue
        return InvoiceItem::select('treatment_id', DB::raw('SUM(quantity) as times_administered'), DB::raw('SUM(total_price) as gross_revenue'))
            ->with('treatment:id,name,price')
            ->groupBy('treatment_id')
            ->orderBy('gross_revenue', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getPatientGrowth(): array
    {
        // Number of patients registered per month over the past 6 months
        return DB::table('users')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(id) as registrations'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->toArray();
    }

    private function getRecallEfficiency(): array
    {
        $totalRecalls = Recall::count();
        if ($totalRecalls === 0) {
            return ['percentage' => 0.00, 'total' => 0, 'scheduled' => 0];
        }

        // Percentage of recalls that successfully turned into a scheduled appointment
        $scheduledRecalls = Recall::where('status', RecallStatus::SCHEDULED)->count();
        $percentage = ($scheduledRecalls / $totalRecalls) * 100;

        return [
            'percentage' => round($percentage, 2),
            'total_recalls_issued' => $totalRecalls,
            'scheduled_appointments' => $scheduledRecalls,
        ];
    }

    private function getDoctorUtilization(): array
    {
        // Doctor patient-visit hours
        return DB::table('appointments')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->select(
                'users.name as doctor_name',
                DB::raw("COUNT(appointments.id) as total_appointments_booked"),
                DB::raw("SUM(TIMESTAMPDIFF(MINUTE, appointments.start_time, appointments.end_time)) as total_booked_minutes")
            )
            ->whereIn('appointments.status', [AppointmentStatus::CONFIRMED->value])
            ->whereNull('appointments.deleted_at')
            ->groupBy('doctors.id', 'users.name')
            ->get()
            ->toArray();
    }
}
