<?php

namespace LaravelModular\Http;

/**
 * Global response factory helpers — usable anywhere.
 */
class ModularResponseFactory
{
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): \Illuminate\Http\JsonResponse
    {
        $payload = ['status' => 'success'];
        if ($message) $payload['message'] = $message;
        if ($data !== null) $payload['data'] = $data;
        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null): \Illuminate\Http\JsonResponse
    {
        $payload = ['status' => 'error', 'message' => $message];
        if ($errors !== null) $payload['errors'] = $errors;
        return response()->json($payload, $status);
    }

    public static function paginated(mixed $paginator, string $message = 'OK'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }
}
