<?php

use Dotenv\Dotenv;
use Illuminate\Foundation\Application;
use App\Http\Middleware\Api\RoleMiddleware;
use App\Http\Middleware\VerifyDevice;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;

$basePath = dirname(__DIR__);

$envFile = file_exists("$basePath/.env.development")
    ? '.env.development'
    : (file_exists("$basePath/.env.production") ? '.env.production' : '.env');

$dotenv = Dotenv::createImmutable($basePath, $envFile);
$dotenv->load();

$app = new Application($basePath);

return $app->configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => RoleMiddleware::class,
            "verify.device" => VerifyDevice::class
        ]);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->group('api', [
            HandleCors::class,
            SubstituteBindings::class,
            ThrottleRequests::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
