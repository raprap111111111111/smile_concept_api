<?php

namespace App\Domain\Dashboards\Services;

use App\Enums\AppointmentStatus;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * Feeds the staff dashboard: headline counters plus the time series the
 * front-end charts render. Kept separate from DashboardService, which answers
 * the clinic-wide financial/analytics question rather than "what is happening
 * today".
 */
class DashboardMetricsService
{
    /** Days of history behind the appointment + activity trend charts. */
    private const TREND_DAYS = 14;

    /** Days of history behind the new-patient trend chart. */
    private const PATIENT_TREND_DAYS = 30;

    /** Months of history behind the new-patient monthly chart. */
    private const PATIENT_TREND_MONTHS = 6;

    /** Clinic opening hours the "today by hour" chart spans. */
    private const DAY_START_HOUR = 7;
    private const DAY_END_HOUR = 20;

    // ═══════════════════════════════════════════════════════
    // STAT CARDS + TRENDS
    // ═══════════════════════════════════════════════════════

    public function getStats(): array
    {
        $today = CarbonImmutable::today();

        $appointmentsToday = Appointment::whereDate('start_time', $today)->count();
        $appointmentsYesterday = Appointment::whereDate('start_time', $today->subDay())->count();

        $newPatients = $this->countPatientsRegisteredBetween(
            $today->startOfMonth(),
            $today->endOfDay()
        );
        $newPatientsPrevious = $this->countPatientsRegisteredBetween(
            $today->subMonth()->startOfMonth(),
            $today->subMonth()->endOfMonth()
        );

        $revenue = $this->sumPaymentsBetween($today->startOfMonth(), $today->endOfDay());
        $revenuePrevious = $this->sumPaymentsBetween(
            $today->subMonth()->startOfMonth(),
            $today->subMonth()->endOfMonth()
        );

        return [
            'appointmentsToday'      => $appointmentsToday,
            'appointmentsTodayDelta' => $this->percentageChange($appointmentsYesterday, $appointmentsToday),

            'newPatients'      => $newPatients,
            'newPatientsDelta' => $this->percentageChange($newPatientsPrevious, $newPatients),

            'pendingReviews' => Appointment::where('status', AppointmentStatus::PENDING->value)->count(),

            'monthlyRevenue'      => $revenue,
            'monthlyRevenueDelta' => $this->percentageChange($revenuePrevious, $revenue),

            'appointmentsTrend'       => $this->getAppointmentsTrend(),
            'appointmentsTodayByHour' => $this->getAppointmentsByHour($today),
            'newPatientsTrend'        => $this->getNewPatientsTrend(),
            'newPatientsByMonth'      => $this->getNewPatientsByMonth(),
        ];
    }

    /**
     * Daily booked/completed/cancelled counts for the last TREND_DAYS days.
     * Gaps are filled with zeroes so the chart keeps an even x-axis.
     */
    private function getAppointmentsTrend(): array
    {
        $end = CarbonImmutable::today();
        $start = $end->subDays(self::TREND_DAYS - 1);

        $rows = Appointment::query()
            ->whereBetween('start_time', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('DATE(start_time) as day')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(status = ?) as completed', [AppointmentStatus::COMPLETED->value])
            ->selectRaw('SUM(status = ?) as cancelled', [AppointmentStatus::CANCELLED->value])
            ->groupBy('day')
            ->get()
            ->keyBy(fn($row) => (string) $row->day);

        $series = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->format('Y-m-d');
            $row = $rows->get($key);

            $series[] = [
                'date'      => $key,
                'label'     => $date->format('M j'),
                'shortLabel' => $date->format('D'),
                'total'     => (int) ($row->total ?? 0),
                'completed' => (int) ($row->completed ?? 0),
                'cancelled' => (int) ($row->cancelled ?? 0),
            ];
        }

        return $series;
    }

    /** Hourly distribution of a single day's appointments, clinic hours only. */
    private function getAppointmentsByHour(CarbonImmutable $day): array
    {
        $counts = Appointment::query()
            ->whereDate('start_time', $day)
            ->selectRaw('HOUR(start_time) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $series = [];
        for ($hour = self::DAY_START_HOUR; $hour <= self::DAY_END_HOUR; $hour++) {
            $series[] = [
                'hour'  => $hour,
                'label' => CarbonImmutable::today()->setTime($hour, 0)->format('g A'),
                'count' => (int) ($counts[$hour] ?? 0),
            ];
        }

        return $series;
    }

    /** Daily patient registrations for the last PATIENT_TREND_DAYS days. */
    private function getNewPatientsTrend(): array
    {
        $end = CarbonImmutable::today();
        $start = $end->subDays(self::PATIENT_TREND_DAYS - 1);

        $counts = $this->patientQuery()
            ->whereBetween('users.created_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('DATE(users.created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->format('Y-m-d');

            $series[] = [
                'date'       => $key,
                'label'      => $date->format('M j'),
                'shortLabel' => $date->format('j'),
                'count'      => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    /** Monthly patient registrations for the last PATIENT_TREND_MONTHS months. */
    private function getNewPatientsByMonth(): array
    {
        $end = CarbonImmutable::today()->startOfMonth();
        $start = $end->subMonths(self::PATIENT_TREND_MONTHS - 1);

        $counts = $this->patientQuery()
            ->whereBetween('users.created_at', [$start, $end->endOfMonth()])
            ->selectRaw("DATE_FORMAT(users.created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $series = [];
        for ($i = 0; $i < self::PATIENT_TREND_MONTHS; $i++) {
            $month = $start->addMonths($i);
            $key = $month->format('Y-m');

            $series[] = [
                'month'      => $key,
                'label'      => $month->format('M Y'),
                'shortLabel' => $month->format('M'),
                'count'      => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    // ═══════════════════════════════════════════════════════
    // TODAY'S SCHEDULE
    // ═══════════════════════════════════════════════════════

    public function getTodaySchedule(): array
    {
        $today = CarbonImmutable::today();

        $appointments = Appointment::query()
            ->with(['user:id,name', 'doctor:id,user_id', 'doctor.user:id,name'])
            ->whereDate('start_time', $today)
            ->orderBy('start_time')
            ->get();

        $statusCounts = $appointments
            ->groupBy(fn($appointment) => $appointment->status?->value ?? 'unknown')
            ->map->count();

        return [
            'date'  => $today->toDateString(),
            'total' => $appointments->count(),

            'appointments' => $appointments->map(fn($appointment) => [
                'id'              => $appointment->id,
                'time'            => $appointment->start_time?->format('g:i A'),
                'startTime'       => $appointment->start_time?->toIso8601String(),
                'endTime'         => $appointment->end_time?->toIso8601String(),
                'durationMinutes' => $appointment->start_time && $appointment->end_time
                    ? $appointment->start_time->diffInMinutes($appointment->end_time)
                    : null,
                'patientName'     => $appointment->user?->name ?? $appointment->patient_name ?? 'Unknown Patient',
                'type'            => $appointment->reason_for_visit ?? 'Consultation',
                'status'          => $appointment->status?->value,
                'doctorName'      => $appointment->doctor?->user?->name,
            ])->values()->all(),

            'byHour' => $this->getAppointmentsByHour($today),

            'byStatus' => collect(AppointmentStatus::cases())
                ->map(fn(AppointmentStatus $status) => [
                    'status' => $status->value,
                    'label'  => ucfirst($status->value),
                    'count'  => (int) ($statusCounts[$status->value] ?? 0),
                ])
                ->all(),
        ];
    }

    // ═══════════════════════════════════════════════════════
    // RECENT ACTIVITY
    // ═══════════════════════════════════════════════════════

    public function getRecentActivity(int $limit = 10): array
    {
        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get();

        return [
            'activities' => $logs->map(fn(ActivityLog $log) => [
                'id'          => $log->id,
                'action'      => $log->action,
                'subjectType' => class_basename((string) $log->subject_type),
                'subjectId'   => $log->subject_id,
                'patientName' => $log->user?->name ?? 'System',
                'description' => $this->describeActivity($log),
                'timeAgo'     => $log->created_at?->diffForHumans(),
                'createdAt'   => $log->created_at?->toIso8601String(),
            ])->values()->all(),

            'byType' => $this->getActivityCountsBySubject(),
            'byDay'  => $this->getActivityTrend(),
        ];
    }

    /** "Created Appointment", "Updated Patient Profile" — a human summary line. */
    private function describeActivity(ActivityLog $log): string
    {
        $subject = class_basename((string) $log->subject_type);
        $subject = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $subject) ?? $subject);

        return trim(ucfirst((string) $log->action) . ' ' . $subject);
    }

    /** Volume per subject type over the trend window — powers the donut. */
    private function getActivityCountsBySubject(): array
    {
        $start = CarbonImmutable::today()->subDays(self::TREND_DAYS - 1)->startOfDay();

        return ActivityLog::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('subject_type, COUNT(*) as total')
            ->groupBy('subject_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'type'  => class_basename((string) $row->subject_type),
                'label' => trim(preg_replace('/(?<!^)[A-Z]/', ' $0', class_basename((string) $row->subject_type)) ?? ''),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /** Daily activity volume for the last TREND_DAYS days, gaps zero-filled. */
    private function getActivityTrend(): array
    {
        $end = CarbonImmutable::today();
        $start = $end->subDays(self::TREND_DAYS - 1);

        $counts = ActivityLog::query()
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->format('Y-m-d');

            $series[] = [
                'date'       => $key,
                'label'      => $date->format('M j'),
                'shortLabel' => $date->format('D'),
                'count'      => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    /**
     * Patients are users holding the `patient` role — there is no separate
     * patients table, so every patient metric funnels through here.
     */
    private function patientQuery()
    {
        return User::query()->role('patient');
    }

    private function countPatientsRegisteredBetween(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return $this->patientQuery()
            ->whereBetween('users.created_at', [$from, $to])
            ->count();
    }

    /**
     * Invoices carry no timestamps in this schema, so realised revenue is read
     * off the payments ledger instead of the invoice total.
     */
    private function sumPaymentsBetween(CarbonImmutable $from, CarbonImmutable $to): float
    {
        return round((float) DB::table('payments')
            ->whereNull('deleted_at')
            ->whereBetween('payment_date', [$from, $to])
            ->sum('amount'), 2);
    }

    /** Signed percentage change, capped so an empty baseline reads as +100%. */
    private function percentageChange(float $previous, float $current): float
    {
        if ($previous <= 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
