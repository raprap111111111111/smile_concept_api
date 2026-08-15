<?php

namespace App\Http\Requests\v1\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAllBranchRequest extends FormRequest
{
    private const DEFAULT_ORDER_BY = 'id';
    private const DEFAULT_ORDER_DIR = 'desc';
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 100;

    public function authorize(): bool
    {
        // Debug: See if the user has the required permission
        // dd($this->user()->can('branches.viewAny')); 
        return $this->user()->can('branch.viewAny');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([

            'order_by' => $this->getValidOrderBy(),
            'order_dir' => $this->getValidOrderDir(),
            'limit' => $this->getValidLimit(),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'min:1', 'max:100'],
            // Opt-in: restrict to branches this user actually works at.
            //
            // Deliberately not the default. Branch pickers on booking and
            // scheduling forms legitimately list every branch, and several
            // roles carry no branch_user rows at all — forcing the scope here
            // would empty those pickers system-wide. Callers that need the
            // narrower list ask for it.
            'mine' => ['nullable', 'boolean'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by' => ['nullable', Rule::in($this->getValidColumns())],
            'order_dir' => ['nullable', Rule::in(['asc', 'desc'])],
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
            'name',
            'address',
            'created_at',
            'updated_at',
        ];
    }
}
