<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        using: function () {
          Route::middleware('web')->group(base_path('routes/web.php'));
          Route::middleware('web')->group(base_path('routes/masterdata.php'));
          Route::middleware('web')->group(base_path('routes/input.php'));
          Route::middleware('web')->group(base_path('routes/report.php'));
          Route::middleware('web')->group(base_path('routes/dashboard.php'));
          Route::middleware('web')->group(base_path('routes/process.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'mandor.access' => \App\Http\Middleware\MandorAccessManagement::class,
        ]);

        // Apply mandor access management globally to web routes
        $middleware->web(append: [
            \App\Http\Middleware\MandorAccessManagement::class,
        ]);

        // CSRF exceptions
        $middleware->validateCsrfTokens(except: [
            'dashboard/mapsapi',
            'api/mobile/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();