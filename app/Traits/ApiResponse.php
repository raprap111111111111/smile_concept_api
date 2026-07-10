<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function responseSuccess($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $code);
    }

    public function responseError(string $message, int $code = 400): JsonResponse
    {
        return response()->json(['message' => $message], $code);
    }
}