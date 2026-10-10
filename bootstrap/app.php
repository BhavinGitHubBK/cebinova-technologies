<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\RespondJsonToAdminAjax;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function () {
            require __DIR__.'/../routes/admin.php';
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->validateCsrfTokens(except: [
            'account/logout',
        ]);
        $middleware->web(append: [
            RespondJsonToAdminAjax::class,
        ]);
        $middleware->alias([
            'admin.active' => EnsureAdminIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
