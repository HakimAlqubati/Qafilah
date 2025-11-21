<?php


namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{

    protected function successResponse(
        mixed  $data = null,
        string $message = 'Success',
        int    $statusCode = 200
    ): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }


    protected function errorResponse(
        string $message = 'Error',
        int    $statusCode = 400,
        mixed  $data = null
    ): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function validationErrorResponse(
        mixed  $errors,
        string $message = 'Validation error',
        int    $statusCode = 422
    ): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $errors,
        ], $statusCode);
    }
}
