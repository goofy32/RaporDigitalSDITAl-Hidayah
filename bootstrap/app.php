<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Konfigurasi global middleware
        $middleware->web(append: [
            // Tambahkan middleware web global
            // Contoh:
            // \App\Http\Middleware\YourCustomMiddleware::class,
        ]);

        $middleware->alias([
            // Definisikan alias middleware
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.guru' => \App\Http\Middleware\AuthenticateGuru::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // Konfigurasi CSRF
        $middleware->validateCsrfTokens(
            except: [
                // Tambahkan rute yang ingin dikecualikan dari CSRF protection
                // 'stripe/*'
            ]
        );

        // Middleware API
        $middleware->api(append: [
            // Tambahkan middleware API global
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();