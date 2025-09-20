<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle all API exceptions with consistent format
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($request, $e);
            }
        });
    }

    /**
     * Handle API exceptions and return a standardized JSON response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return JsonResponse
     */
    private function handleApiException(Request $request, Throwable $exception): JsonResponse
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
    private function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }
        
        if ($exception instanceof ValidationException) {
            return $exception->status;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Get the appropriate message for the exception.
     *
     * @param Throwable $exception
     * @return string
     */
    private function getMessage(Throwable $exception): string
    {
        if ($exception instanceof NotFoundHttpException) {
            return 'The requested resource was not found';
        }
        
        if ($exception instanceof ValidationException) {
            return 'The given data was invalid';
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getMessage() ?: Response::$statusTexts[$exception->getStatusCode()] ?? 'Error';
        }

        return config('app.debug') ? $exception->getMessage() : 'Server Error';
    }

    /**
     * Get validation errors if applicable.
     *
     * @param Throwable $exception
     * @return array|null
     */
    private function getErrors(Throwable $exception): ?array
    {
        if ($exception instanceof ValidationException) {
            return $exception->errors();
        }

        return null;
    }
}