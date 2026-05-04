<?php

namespace LaravelModular\Abstracts;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Base Controller with NestJS-style response helpers.
 */
abstract class AbstractController extends Controller
{
    protected function ok(mixed $data = null, string $message = 'OK'): JsonResponse
    {
        return $this->json($data, $message, 200);
    }

    protected function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->json($data, $message, 201);
    }

    protected function accepted(mixed $data = null, string $message = 'Accepted'): JsonResponse
    {
        return $this->json($data, $message, 202);
    }

    protected function noContent(): Response
    {
        return response()->noContent();
    }

    protected function notFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function badRequest(string $message = 'Bad Request', mixed $errors = null): JsonResponse
    {
        return $this->error($message, 400, $errors);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function conflict(string $message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409);
    }

    protected function tooManyRequests(string $message = 'Too Many Requests'): JsonResponse
    {
        return $this->error($message, 429);
    }

    protected function serverError(string $message = 'Internal Server Error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    protected function unprocessable(mixed $errors): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Unprocessable Entity',
            'errors'  => $errors,
        ], 422);
    }

    protected function json(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        $payload = ['status' => 'success'];
        if ($message) {
            $payload['message'] = $message;
        }
        if ($data !== null) {
            $payload['data'] = $data;
        }
        return response()->json($payload, $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = ['status' => 'error', 'message' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }

    protected function paginated(mixed $paginator, string $message = 'OK'): JsonResponse
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
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
                'path'         => $paginator->path(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ]);
    }

    protected function collection(iterable $items, string $message = 'OK'): JsonResponse
    {
        return $this->json(collect($items)->values(), $message);
    }
}
