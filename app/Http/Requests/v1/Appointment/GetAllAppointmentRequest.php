<?php

namespace App\Http\Requests\v1\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAllAppointmentRequest extends FormRequest
{
    private const DEFAULT_ORDER_BY  = 'start_time';
    private const DEFAULT_ORDER_DIR = 'asc';
    private const DEFAULT_LIMIT     = 10;
    private const MAX_LIMIT         = 100;

    /**
     * 🔐 AUTHORIZATION
     *
     * Allow access if user has EITHER:
     * - appointment.viewAny → Admin/Staff (will see ALL appointments)
     * - appointment.view    → Patient (will see ONLY their own appointments)
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->can('appointment.viewAny') 
            || $user->can('appointment.view');
    }

    /**
     * Check if user can see ALL appointments
     * true  → Admin/Staff
     * false → Patient (filtered to own only)
     */
    public function canViewAny(): bool
    {
        return $this->user()->can('appointment.viewAny');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_by'  => $this->getValidOrderBy(),
            'order_dir' => $this->getValidOrderDir(),
            'limit'     => $this->getValidLimit(),
        ]);
    }

    public function rules(): array
    {
        return [
            'search'     => ['nullable', 'string', 'min:1', 'max:100'],
            'status'     => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
            'doctor_id'  => ['nullable', 'integer', 'exists:doctors,id'],
            'branch_id'  => ['nullable', 'integer', 'exists:branches,id'],
            // 🔐 user_id filter only matters for admin/staff
            // patient's user_id filter is stripped in repository
            'user_id'    => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'offset'     => ['nullable', 'integer', 'min:0'],
            'limit'      => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'   => ['nullable', Rule::in($this->getValidColumns())],
            'order_dir'  => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function getValidOrderBy(): string
    {
        return in_array($this->input('order_by'), $this->getValidColumns())
            ? $this->input('order_by')
            : self::DEFAULT_ORDER_BY;
    }

    protected function getValidOrderDir(): string
    {
        return in_array(strtolower($this->input('order_dir')), ['asc', 'desc'])
            ? strtolower($this->input('order_dir'))
            : self::DEFAULT_ORDER_DIR;
    }

    protected function getValidLimit(): int
    {
        return max(
            1,
            min(self::MAX_LIMIT, (int) $this->input('limit', self::DEFAULT_LIMIT))
        );
    }

    protected function getValidColumns(): array
    {
        return [
            'id',
            'user_id',
            'doctor_id',
            'branch_id',
            'start_time',
            'end_time',
            'status',
            'created_at',
            'updated_at',
        ];
    }
}