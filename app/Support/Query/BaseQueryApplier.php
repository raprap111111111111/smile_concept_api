<?php

namespace App\Support\Query;

use Illuminate\Database\Eloquent\Builder;

class BaseQueryApplier
{
    public static function apply(
        Builder $query,
        array $params,
        array $searchable = [],
        array $sortable = [],
        array $filterable = []
    ): Builder {
        $filters = $params['filters'] ?? [];
        $search = $params['search'] ?? null;
        $orderBy = $params['order_by'] ?? null;
        $orderDir = $params['order_dir'] ?? 'asc';

        static::applySearch($query, $search, $searchable);
        static::applyFilters($query, $filters, $filterable);
        static::applySorting($query, $orderBy, $orderDir, $sortable);

        return $query;
    }

    protected static function applySearch(
        Builder $query,
        ?string $search,
        array $searchable
    ): void {
        if (!$search || empty($searchable)) {
            return;
        }

        $query->where(function (Builder $q) use ($search, $searchable) {
            foreach ($searchable as $field => $mode) {
                if (is_int($field)) {
                    $field = $mode;
                    $mode = 'contains';
                }

                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);

                    $q->orWhereHas($relation, function (Builder $relationQuery) use ($column, $mode, $search) {
                        static::applySearchCondition($relationQuery, $column, $mode, $search);
                    });

                    continue;
                }

                static::applyOrSearchCondition($q, $field, $mode, $search);
            }
        });
    }

    protected static function applySearchCondition(
        Builder $query,
        string $field,
        string $mode,
        string $search
    ): void {
        match ($mode) {
            'exact' => $query->where($field, $search),
            'prefix' => $query->where($field, 'like', $search . '%'),
            default => $query->where($field, 'like', '%' . $search . '%'),
        };
    }

    protected static function applyOrSearchCondition(
        Builder $query,
        string $field,
        string $mode,
        string $search
    ): void {
        match ($mode) {
            'exact' => $query->orWhere($field, $search),
            'prefix' => $query->orWhere($field, 'like', $search . '%'),
            default => $query->orWhere($field, 'like', '%' . $search . '%'),
        };
    }

    protected static function applyFilters(
        Builder $query,
        array $filters,
        array $filterable
    ): void {
        if (empty($filters) || empty($filterable)) {
            return;
        }

        foreach ($filterable as $field => $type) {
            if (is_int($field)) {
                $field = $type;
                $type = 'exact';
            }

            match ($type) {
                'exact' => static::applyExactFilter($query, $filters, $field),
                'in' => static::applyInFilter($query, $filters, $field),
                'range' => static::applyRangeFilter($query, $filters, $field),
                'date_range' => static::applyDateRangeFilter($query, $filters, $field),
                default => static::applyExactFilter($query, $filters, $field),
            };
        }
    }

    protected static function applyExactFilter(
        Builder $query,
        array $filters,
        string $field
    ): void {
        $value = $filters[$field] ?? null;

        if (static::isEmptyFilterValue($value)) {
            return;
        }

        $query->where($field, $value);
    }

    protected static function applyInFilter(
        Builder $query,
        array $filters,
        string $field
    ): void {
        $value = $filters[$field] ?? null;

        if (static::isEmptyFilterValue($value)) {
            return;
        }

        if (is_array($value)) {
            $values = array_values(
                array_filter($value, fn ($item) => !static::isEmptyFilterValue($item))
            );

            if (!empty($values)) {
                $query->whereIn($field, $values);
            }

            return;
        }

        $query->where($field, $value);
    }

    protected static function applyRangeFilter(
        Builder $query,
        array $filters,
        string $field
    ): void {
        $from = $filters[$field . '_from'] ?? null;
        $to = $filters[$field . '_to'] ?? null;

        if (!static::isEmptyFilterValue($from)) {
            $query->where($field, '>=', $from);
        }

        if (!static::isEmptyFilterValue($to)) {
            $query->where($field, '<=', $to);
        }
    }

    protected static function applyDateRangeFilter(
        Builder $query,
        array $filters,
        string $field
    ): void {
        $from = $filters[$field . '_from'] ?? null;
        $to = $filters[$field . '_to'] ?? null;
        $value = $filters[$field] ?? null;

        if (!static::isEmptyFilterValue($value)) {
            $query->whereDate($field, $value);
        }

        if (!static::isEmptyFilterValue($from)) {
            $query->whereDate($field, '>=', $from);
        }

        if (!static::isEmptyFilterValue($to)) {
            $query->whereDate($field, '<=', $to);
        }
    }

    protected static function applySorting(
        Builder $query,
        ?string $orderBy,
        string $orderDir,
        array $sortable
    ): void {
        if (!$orderBy || !in_array($orderBy, $sortable, true)) {
            return;
        }

        $direction = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';

        $query->orderBy($orderBy, $direction);
    }

    protected static function isEmptyFilterValue(mixed $value): bool
    {
        if (is_array($value)) {
            return empty(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        return $value === null || $value === '';
    }
}
