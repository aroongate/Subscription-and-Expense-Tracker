<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiController extends Controller
{
    protected function success(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $payload = $data->resolve();
        } else {
            $payload = $data;
        }

        return response()->json([
            'data' => $payload,
            'meta' => $meta,
        ], $status);
    }

    protected function error(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
