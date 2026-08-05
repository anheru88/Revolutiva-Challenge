<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Src\Shared\Domain\Exception\DomainException;
use Src\Shared\Domain\Exception\EntityNotFoundException;
use Src\Shared\Domain\Exception\InvalidArgumentException;

return Application::configure(basePath: dirname(__DIR__))
    // Sin rutas web: el componente expone solo la API REST (PRD §3).
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->booted(function (): void {
        // Scramble restringe la documentación al entorno `local`. Este componente
        // de demostración la expone también fuera de local (el contenedor corre
        // como `production`), de ahí el gate abierto.
        Gate::define('viewApiDocs', fn ($user = null): bool => true);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (EntityNotFoundException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (InvalidArgumentException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (DomainException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 422);
        });
    })->create();
