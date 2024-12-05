<?php

use App\Http\Helpers\ResponseFormatter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        if (request()->is('api/*')) {
            $exceptions->render(function (Throwable $e) {
                return ResponseFormatter::error(
                    'null',
                    $e->getMessage(),
                    str_contains($e->getMessage(), '[login]') ? 401 : 404,
                );
            });
        }
    })->create();
