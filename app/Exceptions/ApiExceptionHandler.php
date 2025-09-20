<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Handle API exceptions and return a standardized JSON response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return JsonResponse
     */
    public function handle(Request $request, Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception);
        $errors = $this->getErrors($exception);
        
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get the appropriate status code for the exception.
     *
     * @param Throwable $exception
     * @return int
     */
    protected function getStatusCode(Throwable $exception): int
    {
        return match(true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof ValidationException => $exception->status,
            default => 500
        };
    }

    /**
     * Get the appropriate message for the exception.
     *
     * @param Throwable $exception
     * @return string
     */
    protected function getMessage(Throwable $exception): string
    {
        return match(true) {
            $exception instanceof NotFoundHttpException => 'The requested resource was not found',
            $exception instanceof ValidationException => 'The given data was invalid',
            $exception instanceof HttpExceptionInterface => $exception->getMessage() ?: 'Error',
            default => config('app.debug') ? $exception->getMessage() : 'Server Error'
        };
    }

    /**
     * Get validation errors if applicable.
     *
     * @param Throwable $exception
     * @return array|null
     */
    protected function getErrors(Throwable $exception): ?array
    {
        return $exception instanceof ValidationException ? $exception->errors() : null;
    }
}
