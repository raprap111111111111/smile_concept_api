<?php

namespace App\Http\Requests\v1\Consent;

use App\Models\PatientConsent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAllPatientConsentsRequest extends FormRequest
{
    private const DEFAULT_ORDER_BY  = 'signed_at';
    private const DEFAULT_ORDER_DIR = 'desc';
    private const DEFAULT_LIMIT     = 15;
    private const MAX_LIMIT         = 100;

    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PatientConsent::class);
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
        $rules = [
            'search'              => ['nullable', 'string', 'min:1', 'max:100'],
            'status'              => ['nullable', 'string', 'in:valid,voided'],
            'consent_template_id' => ['nullable', 'integer', 'exists:consent_templates,id'],
            'offset'              => ['nullable', 'integer', 'min:0'],
            'limit'               => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'            => ['nullable', Rule::in($this->getValidColumns())],
            'order_dir'           => ['nullable', Rule::in(['asc', 'desc'])],
        ];

        if ($this->user()->can('consent-form.viewAny')) {
            $rules['user_id']        = ['nullable', 'integer', 'exists:users,id'];
            $rules['appointment_id'] = ['nullable', 'integer', 'exists:appointments,id'];
        }

        return $rules;
    }
    protected function getValidOrderBy(): string
    {
        return in_array($this->input('order_by'), $this->getValidColumns(), true)
            ? $this->input('order_by')
            : self::DEFAULT_ORDER_BY;
    }

    protected function getValidOrderDir(): string
    {
        return in_array(strtolower((string) $this->input('order_dir')), ['asc', 'desc'], true)
            ? strtolower((string) $this->input('order_dir'))
            : self::DEFAULT_ORDER_DIR;
    }

    protected function getValidLimit(): int
    {
        return max(1, min(self::MAX_LIMIT, (int) $this->input('limit', self::DEFAULT_LIMIT)));
    }

    protected function getValidColumns(): array
    {
        return ['id', 'signed_at', 'created_at'];
    }
}
