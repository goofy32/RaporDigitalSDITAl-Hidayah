<?php

use App\Http\Middleware\CheckMataPelajaranOwnership;
use App\Http\Middleware\SessionTimeout;
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
            SessionTimeout::class,
        ]);


        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.guru' => \App\Http\Middleware\AuthenticateGuru::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'session.timeout' => SessionTimeout::class, // Optional: jika ingin digunakan sebagai alias
            'check.matapelajaran.ownership' => CheckMataPelajaranOwnership::class // Tambahkan ini
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