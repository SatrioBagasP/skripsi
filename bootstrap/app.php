<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Validation\UnauthorizedException;
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
        $middleware->append(EnsureTokenIsValid::class);
        $middleware->alias([
            'isAdmin' => App\Http\Middleware\isAdmin::class,
            'isUnitMahasiswa' => App\Http\Middleware\isUnitMahasiswa::class,
            'isDosen' => App\Http\Middleware\isDosen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
