<?php

namespace App\Domain\Appointments\Repositories;

use App\Domain\Settings\DTOs\AppointmentSettings;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Support\Query\BaseRepository;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Appointments\DTOs\CalendarCountsAppointmentDTO;

class AppointmentRepository extends BaseRepository
{
    public function __construct(
        private readonly AppointmentSettings $settings,
    ) {}

    protected string $model = Appointment::class;

    protected array $searchable = [
        'id',
        'user_id',
        'doctor_id',
        'status',
        'reason_for_visit',
        'cancellation_reason',
    ];

    protected array $filterable = [
        'status',
        'branch_id',
        'doctor_id',
        'user_id',
        'reminder_sent',
        'created_by',
    ];

    protected array $sortable = [
        'id',
        'start_time',
        'end_time',
        'status',
        'created_at',
    ];

    protected string $defaultOrderBy = 'start_time';
    protected string $defaultOrderDirection = 'desc';

    /**
     * 🔐 PAGINATION WITH PERMISSION-BASED FILTERING
     *
     * Logic:
     * - canViewAny = true  → Admin/Staff → Show ALL appointments
     * - canViewAny = false → Patient     → Show ONLY their own appointments
     *                        Also removes user_id from params to prevent
     *                        patient from filtering by someone else's user_id
     */
    public function paginate(
        array $params = [],
        ?string $resourceClass = null,
        bool $canViewAny = false,
        ?int $userId = null
    ): array {
        $query = $this->model::query()
            ->with([
                'user',
                'doctor.user',
                'branch',
                'creator',
                'invoice',
            ]);

        // 🔐 CORE PERMISSION LOGIC
        if ($canViewAny) {
            // Admin/Staff → No filter, show ALL appointments
            // They can still filter by user_id via params if needed
        } else {
            // Patient → Force filter to ONLY their own appointments
            $query->where('user_id', $userId);

            // 🛡️ Security: Remove user_id from params
            // Prevents patient from passing user_id=X to see someone else's appointments
            unset($params['user_id']);
        }

        // Search
        if (!empty($params['search'])) {
            $search = $params['search'];

            $query->where(function ($q) use ($search) {
                $q->where('reason_for_visit', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('doctor.user', function ($doctorUserQuery) use ($search) {
                        $doctorUserQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('branch', function ($branchQuery) use ($search) {
                        $branchQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('branch_code', 'like', "%{$search}%");
                    });
            });
        }

        // Exact filters
        foreach ($this->filterable as $field) {
            if (
                array_key_exists($field, $params) &&
                $params[$field] !== null &&
                $params[$field] !== ''
            ) {
                $query->where($field, $params[$field]);
            }
        }

        // Date range filters
        if (!empty($params['start_date'])) {
            $query->whereDate('start_time', '>=', $params['start_date']);
        }

        if (!empty($params['end_date'])) {
            $query->whereDate('start_time', '<=', $params['end_date']);
        }

        // Sorting
        $orderBy = $params['order_by'] ?? $this->defaultOrderBy;
        $orderDir = $params['order_dir'] ?? $this->defaultOrderDirection;

        if (!in_array($orderBy, $this->sortable, true)) {
            $orderBy = $this->defaultOrderBy;
        }

        if (!in_array(strtolower($orderDir), ['asc', 'desc'], true)) {
            $orderDir = $this->defaultOrderDirection;
        }

        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $total = (clone $query)->count();

        $records = $query
            ->offset($offset)
            ->limit($limit)
            ->get();

        if ($resourceClass && is_subclass_of($resourceClass, JsonResource::class)) {
            $records = $resourceClass::collection($records);
        }

        return [
            'records'      => $records,
            'total'        => $total,
            'offset'       => $offset,
            'limit'        => $limit,
            'current_page' => (int) floor($offset / max($limit, 1)) + 1,
            'last_page'    => (int) ceil($total / max($limit, 1)),
            'per_page'     => $limit,
            'has_more'     => ($offset + $limit) < $total,
        ];
    }

    /**
     * 🔐 FIND SINGLE APPOINTMENT BY ID
     *
     * Logic:
     * - canViewAny = true  → Admin/Staff → Can open/view ANY appointment detail
     * - canViewAny = false → Patient     → Can ONLY open/view their OWN appointment detail
     *                        Returns 403 if they try to access someone else's
     */
    public function findOrFail(
        int $id,
        bool $canViewAny = false,
        ?int $userId = null
    ): Appointment {
        $appointment = $this->model::with([
            'user',
            'doctor.user',
            'branch',
            'creator',
            'invoice',
        ])->findOrFail($id);

        // 🔐 CORE PERMISSION LOGIC
        if ($canViewAny) {
            // Admin/Staff → Can view any appointment detail
            return $appointment;
        }

        // Patient → Can ONLY view their own appointment detail
        if ((int) $appointment->user_id !== (int) $userId) {
            abort(403, 'You can only view your own appointments.');
        }

        return $appointment;
    }

    /**
     * Check if doctor already has a conflicting appointment.
     */
    public function checkConflicts(
        int $doctorId,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): bool {
        $query = $this->model::query()
            ->where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($overlap) use ($startTime, $endTime) {
                    $overlap->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getDoctorSchedule(int $doctorId, string $startDate, string $endDate)
    {
        return $this->model::query()
            ->where('doctor_id', $doctorId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['user', 'branch'])
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function getUserAppointments(int $userId, ?string $status = null)
    {
        $query = $this->model::query()
            ->where('user_id', $userId)
            ->with(['doctor.user', 'branch']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('start_time', 'desc')->get();
    }

    /**
     * Build the bookable slot grid for one doctor, branch, and date.
     *
     * Every configurable rule that shapes the grid comes from
     * AppointmentSettings. Each blocked slot carries `unavailable_reason` —
     * the setting key (or 'booked'/'past') that blocked it — so "why is this
     * slot gone" is answerable from the API response alone.
     */
    public function getAvailableSlots(int $doctorId, int $branchId, string $date): array
    {
        $carbonDate = Carbon::parse($date);

        $empty = [
            'date'      => $date,
            'doctor_id' => $doctorId,
            'branch_id' => $branchId,
            'slots'     => [],
        ];

        if (!$this->settings->isWorkingDay($carbonDate)) {
            return $empty;
        }

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('day_of_week', $carbonDate->dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return $empty;
        }

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('start_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['start_time', 'end_time']);

        // Concurrency is clinic-wide: every doctor's bookings overlapping this
        // date count against max_concurrent_appointments.
        $clinicWide = Appointment::whereDate('start_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['start_time', 'end_time']);

        [$clinicOpen, $clinicClose] = $this->settings->clinicWindowFor($carbonDate);

        $slotMinutes   = $this->settings->slotDurationMinutes;
        $strideMinutes = $this->settings->slotStrideMinutes();
        $earliest      = $this->settings->earliestBookableAt();
        $latest        = $this->settings->latestBookableAt();

        $slots = [];

        foreach ($schedules as $schedule) {
            // Doctor's window clamped to clinic hours.
            $start = Carbon::parse($date . ' ' . $schedule->start_time)->max($clinicOpen);
            $end   = Carbon::parse($date . ' ' . $schedule->end_time)->min($clinicClose);

            while ($start->lt($end)) {
                $slotEnd = $start->copy()->addMinutes($slotMinutes);

                if ($slotEnd->gt($end)) {
                    break;
                }

                $reason = $this->slotBlockReason(
                    $start, $slotEnd, $appointments, $clinicWide, $earliest, $latest
                );

                $slots[] = [
                    'start_time'         => $start->toDateTimeString(),
                    'end_time'           => $slotEnd->toDateTimeString(),
                    'is_available'       => $reason === null,
                    'unavailable_reason' => $reason,
                ];

                $start->addMinutes($strideMinutes);
            }
        }

        return [
            'date'      => $date,
            'doctor_id' => $doctorId,
            'branch_id' => $branchId,
            'slots'     => $slots,
        ];
    }

    /**
     * First rule that blocks a slot, or null when it is bookable.
     * Checks run cheapest-first; ordering is otherwise cosmetic.
     */
    private function slotBlockReason(
        Carbon $start,
        Carbon $slotEnd,
        $doctorAppointments,
        $clinicWideAppointments,
        Carbon $earliest,
        Carbon $latest,
    ): ?string {
        if ($start->isPast()) {
            return 'past';
        }

        if ($this->settings->overlapsLunch($start, $slotEnd)) {
            return 'lunch_break_start';
        }

        if ($start->lt($earliest)) {
            return $this->settings->allowSameDayBooking
                ? 'booking_lead_time_hours'
                : 'allow_same_day_booking';
        }

        if ($start->gt($latest)) {
            return 'max_advance_booking_days';
        }

        $overlaps = fn($appointment) =>
            $start->lt(Carbon::parse($appointment->end_time))
            && $slotEnd->gt(Carbon::parse($appointment->start_time));

        if ($doctorAppointments->contains($overlaps)) {
            return 'booked';
        }

        $concurrent = $clinicWideAppointments->filter($overlaps)->count();
        if ($concurrent >= $this->settings->maxConcurrent) {
            return 'max_concurrent_appointments';
        }

        return null;
    }

    public function getCalendarCounts(
        CalendarCountsAppointmentDTO $dto,
        bool $canViewAny = false,   // ✅ NEW
        ?int $authUserId = null      // ✅ NEW
    ): array {
        // Anchor to day 01: 'Y-m' alone inherits today's day and overflows short months.
        $startOfMonth = Carbon::createFromFormat('Y-m-d', $dto->month.'-01')
            ->startOfDay();

        $endOfMonth = $startOfMonth->copy()
            ->endOfMonth()
            ->endOfDay();

        // Clinic scope means day load for every caller, not just patients:
        // the booking calendar must show the same busy dots to whoever opens
        // it. Staff keep their full breakdown via the default 'own' scope.
        $isDayLoad = $dto->scope === CalendarCountsAppointmentDTO::SCOPE_CLINIC;

        $query = $this->model::query()
            ->whereBetween('start_time', [$startOfMonth, $endOfMonth]);

        // 🔐 CORE PERMISSION LOGIC (same as paginate)
        if ($isDayLoad) {
            // Cancelled appointments free the slot, so they don't make a day busy.
            $query->where('status', '!=', AppointmentStatus::CANCELLED->value);
        } elseif (!$canViewAny) {
            // Patient → force filter to their own appointments
            $query->where('user_id', $authUserId);
        } elseif ($dto->userId !== null) {
            // Admin/Staff can optionally filter by user_id
            $query->where('user_id', $dto->userId);
        }

        // status and user_id are ignored for day load: narrowing clinic-wide
        // counts by either would let a patient probe other people's bookings.
        if ($dto->status !== null && !$isDayLoad) {
            $query->where('status', $dto->status);
        }

        if ($dto->doctorId !== null) {
            $query->where('doctor_id', $dto->doctorId);
        }

        if ($dto->branchId !== null) {
            $query->where('branch_id', $dto->branchId);
        }

        $rows = $query
            ->selectRaw('DATE(start_time) as appointment_date, status, COUNT(*) as total')
            ->groupByRaw('DATE(start_time), status')
            ->orderBy('appointment_date')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $date = $row->appointment_date;
            $status = $row->status instanceof \App\Enums\AppointmentStatus
                ? $row->status->value
                : (string) $row->status;
            $count = (int) $row->total;

            if (!isset($result[$date])) {
                $result[$date] = [
                    'pending'   => 0,
                    'confirmed' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'total'     => 0,
                ];
            }

            if (array_key_exists($status, $result[$date])) {
                $result[$date][$status] = $count;
            }

            $result[$date]['total'] += $count;
        }

        // Strip the per-status breakdown so day load discloses volume only.
        if ($isDayLoad) {
            return array_map(
                static fn (array $counts): array => ['total' => $counts['total']],
                $result,
            );
        }

        return $result;
    }
}
