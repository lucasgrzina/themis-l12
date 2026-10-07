<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // Réplica exacta de la vieja App\Providers\RouteServiceProvider::map():
            // mismo namespace por grupo, mismo prefijo/nombre de rutas de API,
            // Y el mismo ORDEN (api antes que web). web.php termina en un
            // catch-all Route::any('{all}')->where(['all' => '.*']) que
            // matchea cualquier path -- si se registra primero, se come las
            // rutas de la API antes de que el router llegue a mirarlas.
            // Route::namespace() sigue funcionando en L12 vía el passthru de
            // RouteRegistrar, aunque ya no aparece como método propio del Router.
            Route::prefix('api')
                ->middleware(['api', 'throttle:60,1'])
                ->as('api.')
                ->namespace('App\Http\Controllers\API')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
