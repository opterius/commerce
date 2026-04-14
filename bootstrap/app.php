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
        $middleware->alias([
            'staff'     => \App\Http\Middleware\StaffMiddleware::class,
            'staff.can' => \App\Http\Middleware\StaffCan::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Redirect unauthenticated users to the correct login page per guard
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*') || $request->is('admin')) {
                return route('staff.login');
            }
            return route('client.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
