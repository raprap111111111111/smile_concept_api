<?php

namespace App\Http\Requests\v1\PatientAttachment;

use App\Models\PatientAttachment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class GetPatientFolderRequest extends FormRequest
{
    private const DEFAULT_ORDER_BY  = 'created_at';
    private const DEFAULT_ORDER_DIR = 'desc';
    private const DEFAULT_LIMIT     = 15;
    private const MAX_LIMIT         = 100;

    public function authorize(): bool
    {
        return Gate::allows('viewAny', PatientAttachment::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_by'        => $this->getValidOrderBy(),
            // ✅ FIX: match what BaseQueryApplier reads
            'order_direction' => $this->getValidOrderDir(),
            'limit'           => $this->getValidLimit(),
        ]);
    }

    public function rules(): array
    {
        return [
            'search'          => ['nullable', 'string', 'min:1', 'max:100'],
            'file_type'       => ['nullable', 'string', 'in:jpg,jpeg,png,pdf,dcm'],
            'category'        => ['nullable', 'string', 'in:xray,photo,consent_form,treatment_plan,lab_report,prescription,referral,other'],
            'is_xray'         => ['nullable', 'boolean'],
            'scan_status'     => ['nullable', 'string', 'in:pending,processing,completed,failed,not_applicable'],
            'offset'          => ['nullable', 'integer', 'min:0'],
            'limit'           => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'        => ['nullable', Rule::in($this->getValidColumns())],
            // ✅ FIX: match key name
            'order_direction' => ['nullable', Rule::in(['asc', 'desc'])],
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
        // ✅ Check both old and new key name for safety
        $dir = $this->input('order_direction') ?? $this->input('order_dir') ?? '';

        return in_array(strtolower($dir), ['asc', 'desc'])
            ? strtolower($dir)
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