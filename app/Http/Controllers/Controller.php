<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\ApiResponseHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @method \Illuminate\Http\JsonResponse successResponse($data, string $message = 'Success', int $statusCode = 200)
 * @method \Illuminate\Http\JsonResponse errorResponse(string $message = 'Error', int $statusCode = 400, $data = [])
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponseHelper,  ApiResponse;
}
