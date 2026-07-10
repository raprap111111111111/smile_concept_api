<?php

namespace App\Domain\Appointments\Repositories;

use App\Models\Appointment;
use App\Support\Query\BaseRepository;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Appointments\DTOs\CalendarCountsAppointmentDTO;

class AppointmentRepository extends BaseRepository
{
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

    public function getAvailableSlots(int $doctorId, int $branchId, string $date): array
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek  = $carbonDate->dayOfWeek;

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return [
                'date'      => $date,
                'doctor_id' => $doctorId,
                'branch_id' => $branchId,
                'slots'     => [],
            ];
        }

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('branch_id', $branchId)
            ->whereDate('start_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['start_time', 'end_time']);

        $slots        = [];
        $slotDuration = 30;

        foreach ($schedules as $schedule) {
            $start = Carbon::parse($date . ' ' . $schedule->start_time);
            $end   = Carbon::parse($date . ' ' . $schedule->end_time);

            while ($start->lt($end)) {
                $slotEnd = $start->copy()->addMinutes($slotDuration);

                if ($slotEnd->gt($end)) {
                    break;
                }

                $isBooked = $appointments->contains(function ($appointment) use ($start, $slotEnd) {
                    $appointmentStart = Carbon::parse($appointment->start_time);
                    $appointmentEnd   = Carbon::parse($appointment->end_time);

                    return $start->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
                });

                $slots[] = [
                    'start_time'   => $start->toDateTimeString(),
                    'end_time'     => $slotEnd->toDateTimeString(),
                    'is_available' => !$isBooked && !$start->isPast(),
                ];

                $start->addMinutes($slotDuration);
            }
        }

        return [
            'date'      => $date,
            'doctor_id' => $doctorId,
            'branch_id' => $branchId,
            'slots'     => $slots,
        ];
    }

    public function getCalendarCounts(
        CalendarCountsAppointmentDTO $dto,
        bool $canViewAny = false,   // ✅ NEW
        ?int $authUserId = null      // ✅ NEW
    ): array {
        $startOfMonth = Carbon::createFromFormat('Y-m', $dto->month)
            ->startOfMonth()
            ->startOfDay();

        $endOfMonth = Carbon::createFromFormat('Y-m', $dto->month)
            ->endOfMonth()
            ->endOfDay();

        $query = $this->model::query()
            ->whereBetween('start_time', [$startOfMonth, $endOfMonth]);

        // 🔐 CORE PERMISSION LOGIC (same as paginate)
        if (!$canViewAny) {
            // Patient → force filter to their own appointments
            $query->where('user_id', $authUserId);
        } elseif ($dto->userId !== null) {
            // Admin/Staff can optionally filter by user_id
            $query->where('user_id', $dto->userId);
        }

        if ($dto->status !== null) {
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

        return $result;
    }
}
