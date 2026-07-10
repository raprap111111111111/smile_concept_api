<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseHelper
{
    /**
     * Return a standardized success JSON response.
     */
    public function successResponse($data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return a standardized error JSON response.
     */
    public function errorResponse(string $message = 'Error', int $statusCode = 400, $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
