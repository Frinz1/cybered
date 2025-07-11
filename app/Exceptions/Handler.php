<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        
        // Custom JSON response for AJAX requests
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;
                
                // Log the error
                Log::error('JSON request error: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
                
                return new JsonResponse([
                    'error' => $e->getMessage(),
                    'status' => $statusCode,
                ], $statusCode);
            }
            
            return null;
        });
    }
}