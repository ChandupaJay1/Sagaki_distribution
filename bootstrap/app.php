<?php

if (!function_exists('mb_split') && file_exists(__DIR__ . '/../vendor/symfony/polyfill-mbstring/bootstrap.php')) {
    require_once __DIR__ . '/../vendor/symfony/polyfill-mbstring/bootstrap.php';
}

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check.status' => \App\Http\Middleware\CheckAccountStatus::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CheckAccountStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
