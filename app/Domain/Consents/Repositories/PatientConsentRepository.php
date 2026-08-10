<?php

namespace App\Domain\Consents\Repositories;

use App\Models\PatientConsent;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PatientConsentRepository extends BaseRepository
{
    protected string $model = PatientConsent::class;

    protected array $relations = [
        'template',
        'patient',
        'appointment',
        'signedByStaff',
    ];

    protected array $searchable = [];

    protected array $filterable = [
        'user_id',
        'appointment_id',
        'consent_template_id',
    ];

    protected array $sortable = [
        'id',
        'signed_at',
        'created_at',
    ];

    protected string $defaultOrderBy        = 'signed_at';
    protected string $defaultOrderDirection = 'desc';

    /**
     * Custom paginate: fully overrides the parent to add support for
     *   - patient/template search
     *   - status filter (valid | voided)
     *   - permission-based user_id scoping
     */
    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Patients only see their own consents
        if ($user && ! $user->can('consent-form.viewAny')) {
            $params['user_id'] = $user->id;
        }

        // Pull out custom params BEFORE calling parent so it doesn't choke
        $search = $params['search'] ?? null;
        $status = $params['status'] ?? null;
        unset($params['search'], $params['status']);

        // Use the parent to handle the base query but intercept via
        // a scoped local query variable — build manually here.
        $query = ($this->model)::query()->with($this->relations);

        // ── filterable columns (user_id, appointment_id, consent_template_id)
        foreach ($this->filterable as $field) {
            if (isset($params[$field]) && $params[$field] !== null && $params[$field] !== '') {
                $query->where($field, $params[$field]);
            }
        }

        // ── custom search (patient name / email + template title)
        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('template', function (Builder $t) use ($term) {
                    $t->where('title', 'like', "%{$term}%");
                })->orWhereHas('patient', function (Builder $p) use ($term) {
                    $p->where('name',  'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%");
                });
            });
        }

        // ── status filter
        if ($status === 'valid') {
            $query->whereNull('voided_at');
        } elseif ($status === 'voided') {
            $query->whereNotNull('voided_at');
        }

        // ── sorting
        $orderBy = in_array($params['order_by'] ?? null, $this->sortable, true)
            ? $params['order_by']
            : $this->defaultOrderBy;

        $orderDir = strtolower((string) ($params['order_dir'] ?? $this->defaultOrderDirection));
        if (! in_array($orderDir, ['asc', 'desc'], true)) {
            $orderDir = $this->defaultOrderDirection;
        }
        $query->orderBy($orderBy, $orderDir);

        // ── pagination
        $limit  = max(1, min(100, (int) ($params['limit']  ?? 15)));
        $offset = max(0, (int) ($params['offset'] ?? 0));
        $page   = (int) floor($offset / $limit) + 1;

        $paginator = $query->paginate(
            perPage: $limit,
            columns: ['*'],
            pageName: 'page',
            page:    $page,
        );

        // ── resource wrapping
        $records = $resourceClass
            ? $resourceClass::collection($paginator->items())
            : $paginator->items();

        return [
            'records'      => $records,
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'has_more'     => $paginator->hasMorePages(),
        ];
    }

    public function forAppointment(int $appointmentId)
    {
        return ($this->model)::with(['template', 'signedByStaff', 'patient'])
            ->where('appointment_id', $appointmentId)
            ->orderByDesc('signed_at')
            ->get();
    }
}