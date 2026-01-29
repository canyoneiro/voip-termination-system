<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function success(mixed $data = null, array $meta = [], int $code = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    protected function error(string $message, string $code = 'ERROR', array $details = [], int $httpCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $httpCode);
    }

    protected function paginated($paginator): JsonResponse
    {
        return $this->success(
            $paginator->items(),
            [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ]
        );
    }

    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 'NOT_FOUND', [], 404);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 'UNAUTHORIZED', [], 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 'FORBIDDEN', [], 403);
    }

    protected function validationError(array $errors): JsonResponse
    {
        return $this->error('Validation failed', 'VALIDATION_ERROR', $errors, 422);
    }
}
