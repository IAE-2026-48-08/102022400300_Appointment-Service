<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'iae.key' => \App\Http\Middleware\CheckApiKey::class,
        'sso.jwt' => \App\Http\Middleware\VerifySsoJwt::class,
    ]);
        //
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof NotFoundHttpException => 404,
                $exception instanceof MethodNotAllowedHttpException => 405,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            $errors = $exception instanceof ValidationException
                ? $exception->errors()
                : null;

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'Server error',
                'data' => null,
                'errors' => $errors,
            ], $status);
        });
    })->create();
