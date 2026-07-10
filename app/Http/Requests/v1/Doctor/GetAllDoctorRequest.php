<?php
// app/Http/Requests/v1/Doctor/GetAllDoctorRequest.php

namespace App\Http\Requests\v1\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAllDoctorRequest extends FormRequest
{
    private const DEFAULT_ORDER_BY = 'id';
    private const DEFAULT_ORDER_DIR = 'desc';
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 100;

    public function authorize(): bool
    {
        return $this->user()->can('doctor.viewAny');
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
            'search'         => ['nullable', 'string', 'min:1', 'max:100'],
            'limit'          => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'       => ['nullable', Rule::in($this->getValidColumns())],
            'order_dir'      => ['nullable', Rule::in(['asc', 'desc'])],
            
            // ✅ NEW filters
            'specialization' => ['nullable', 'string', 'max:100'],
            'is_active'      => ['nullable', 'boolean'],
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
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
        return max(1, min(self::MAX_LIMIT, (int) $this->input('limit', self::DEFAULT_LIMIT)));
    }

    protected function getValidColumns(): array
    {
        return [
            'id',
            'specialization',
            'license_number',        // ✅ ADD
            'years_of_experience',   // ✅ ADD
            'consultation_fee',      // ✅ ADD
            'created_at',
        ];
    }
}