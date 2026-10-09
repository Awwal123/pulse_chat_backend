<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {
            Broadcast::routes([
                'middleware' => ['auth:sanctum'],
            ]);

            require base_path('routes/channels.php');

            // TEMP DEBUG (remove after reading the result): shows the active broadcaster.
            // Prints only class names and true/false flags, no secret values.
            \Illuminate\Support\Facades\Route::get('/_bd', function () {
                return response()->json([
                    'default' => config('broadcasting.default'),
                    'driver_class' => get_class(
                        app(\Illuminate\Contracts\Broadcasting\Factory::class)->driver()
                    ),
                    'config_cached' => app()->configurationIsCached(),
                    'routes_cached' => app()->routesAreCached(),
                    'reverb_key_set' => filled(config('broadcasting.connections.reverb.key')),
                    'reverb_secret_set' => filled(config('broadcasting.connections.reverb.secret')),
                    'reverb_app_id_set' => filled(config('broadcasting.connections.reverb.app_id')),
                ]);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->validateCsrfTokens(except: [
            'broadcasting/auth',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();