<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Always respond with JSON for API routes — no HTML error pages.
        $jsonResponse = static function (string $message, int $status, mixed $errors = null): JsonResponse {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => $errors,
            ], $status);
        };

        // 422 — Validation errors.
        $exceptions->render(function (ValidationException $e): JsonResponse {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        });

        // 404 — Route or model not found.
        $exceptions->render(function (NotFoundHttpException $e) use ($jsonResponse): JsonResponse {
            $message = $e->getMessage() ?: 'The requested resource was not found.';
            return $jsonResponse($message, 404);
        });

        // 401 — Unauthenticated (Sanctum / session guard).
        $exceptions->render(function (AuthenticationException $e) use ($jsonResponse): JsonResponse {
            return $jsonResponse('Unauthenticated. Please log in to continue.', 401);
        });

        // Catch-all for remaining Symfony HTTP exceptions (403, 405, 429, etc.).
        $exceptions->render(function (HttpException $e) use ($jsonResponse): JsonResponse {
            $message = $e->getMessage() ?: 'An HTTP error occurred.';
            return $jsonResponse($message, $e->getStatusCode());
        });

    })->create();
