<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseHelper
{
    public function responseSuccess(mixed $data = null, string $message = 'Operation successful.', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    public function responsePaginated(
        LengthAwarePaginator $paginator,
        object $dto,
        ?string $resourceClass = null,
        string $message = 'Data retrieved successfully.'
    ): JsonResponse {
        $items = $paginator->items();

        $data = [
            'total'        => $paginator->total(),
            'records'      => $resourceClass && !empty($items) ? $resourceClass::collection($items) : $items,
            'offset'       => $dto->offset ?? 0,
            'limit'        => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'has_more'     => $paginator->hasMorePages(),
        ];

        return $this->responseSuccess($data, $message);
    }
}