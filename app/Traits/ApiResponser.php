<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Return a successful JSON response.
     *
     * @param  mixed       $data
     * @param  string|null $message
     * @param  int         $code    HTTP status code (default 200)
     */
    protected function success(mixed $data, ?string $message = null, int $code = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($payload, $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string     $message
     * @param  int        $code    HTTP status code
     * @param  mixed|null $errors  Validation errors or extra debug info
     */
    protected function error(string $message, int $code, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ];

        return response()->json($payload, $code);
    }
}
