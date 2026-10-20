<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Los invitados al panel del empleado van a su propio login.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('empleado', 'empleado/*')
            ? route('empleado.login')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
