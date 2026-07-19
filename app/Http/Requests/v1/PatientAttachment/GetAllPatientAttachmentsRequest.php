<?php

namespace App\Http\Requests\v1\PatientAttachment;

use App\Models\PatientAttachment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAllPatientAttachmentsRequest extends FormRequest
{
    private const DEFAULT_ORDER_BY  = 'created_at';
    private const DEFAULT_ORDER_DIR = 'desc';
    private const DEFAULT_LIMIT     = 15;
    private const MAX_LIMIT         = 100;

    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PatientAttachment::class);
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
            'user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],

            // ✅ FIXED: match actual file extensions in your DB
            'file_type'      => ['nullable', 'string', 'in:jpg,jpeg,png,pdf,dcm'],

            // ✅ ADDED: category filter (matches your migration enum)
            'category'       => ['nullable', 'string', 'in:xray,photo,consent_form,treatment_plan,lab_report,prescription,referral,other'],

            // ✅ ADDED: X-ray filter
            'is_xray'        => ['nullable', 'boolean'],

            // ✅ ADDED: scan status filter
            'scan_status'    => ['nullable', 'string', 'in:pending,processing,completed,failed,not_applicable'],

            'offset'         => ['nullable', 'integer', 'min:0'],
            'limit'          => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'       => ['nullable', Rule::in($this->getValidColumns())],
            'order_dir'      => ['nullable', Rule::in(['asc', 'desc'])],
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
        return in_array(strtolower($this->input('order_dir') ?? ''), ['asc', 'desc'])
            ? strtolower($this->input('order_dir'))
            : self::DEFAULT_ORDER_DIR;
    }

    protected function getValidLimit(): int
    {
        return max(1, min(self::MAX_LIMIT, (int) $this->input('limit', self::DEFAULT_LIMIT)));
    }

    protected function getValidColumns(): array
    {
        return ['id', 'file_name', 'file_type', 'category', 'created_at', 'scanned_at'];
    }
}