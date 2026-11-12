<?php

use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'cleanup.matricula.draft' => \App\Http\Middleware\CleanupMatriculaDraft::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión expirada. Vuelve a iniciar sesión.'], 419);
            }
            return redirect()->route('login')->with('status', 'Tu sesión expiró. Vuelve a iniciar sesión.');
        });

        $exceptions->render(function (MissingAppKeyException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Error de servidor. Vuelve a iniciar sesión.'], 500);
            }
            return redirect()->route('login')->with('status', 'Se reinició el servidor. Vuelve a iniciar sesión.');
        });
    })->create();
