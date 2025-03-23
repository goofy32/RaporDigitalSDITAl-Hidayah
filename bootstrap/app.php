<?php

use App\Http\Middleware\CheckMataPelajaranOwnership;
use App\Http\Middleware\SessionTimeout;
use App\Http\Middleware\CacheControl;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TahunAjaranMiddleware;

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
            CacheControl::class,
            TahunAjaranMiddleware::class
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.guru' => \App\Http\Middleware\AuthenticateGuru::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'session.timeout' => SessionTimeout::class,
            'check.matapelajaran.ownership' => CheckMataPelajaranOwnership::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'check.wali.kelas' => \App\Http\Middleware\CheckWaliKelas::class,
            'check.report.template' => \App\Http\Middleware\CheckReportTemplate::class,
            'check.rapor.access' => \App\Http\Middleware\CheckRaporAccess::class,
            'tahun.ajaran' => TahunAjaranMiddleware::class,
        ]);

        // Konfigurasi CSRF
        $middleware->validateCsrfTokens(
            except: [
                // Tambahkan rute yang ingin dikecualikan dari CSRF protection
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