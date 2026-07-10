<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

trait HasPagination
{
    /**
     * Format paginated response consistently across the application
     */
    protected function formatPaginatedResponse(
        LengthAwarePaginator $paginator, 
        ?string $resourceClass = null
    ): array {
        $items = $resourceClass 
            ? $resourceClass::collection($paginator->items()) 
            : $paginator->items();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ]
        ];
    }
}