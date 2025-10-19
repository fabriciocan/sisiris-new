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
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar middlewares personalizados para controle de acesso
        $middleware->alias([
            'assembleia.access' => \App\Http\Middleware\CheckAssembleiaAccess::class,
            'jurisdiction.permissions' => \App\Http\Middleware\CheckJurisdictionPermissions::class,
            'protect.sensitive' => \App\Http\Middleware\ProtectSensitiveOperations::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
