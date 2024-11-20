<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(
            except: ['/login']
        );

        // Register the middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'auth.guru' => \App\Http\Middleware\AuthenticateGuru::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();