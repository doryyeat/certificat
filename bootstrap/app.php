<?php

use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SubscriptionMiddleware;
use App\Http\Middleware\VerifiedBusinessMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Регистрируем middleware алиасы
        $middleware->alias([
            'user.type' => CheckUserType::class,
            'role' => RoleMiddleware::class,
            'verified.business' => VerifiedBusinessMiddleware::class,
            'subscription' => SubscriptionMiddleware::class,
        ]);

        // Добавляем middleware для web группы
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'client-type/set', // ваш маршрут
            'api/*',
/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
