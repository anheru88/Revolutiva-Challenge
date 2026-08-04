<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scramble restringe la documentación al entorno `local`. En este
        // componente de demostración exponemos la API docs también fuera de
        // local (p. ej. el contenedor Docker corre como `production`).
        Gate::define('viewApiDocs', fn ($user = null): bool => true);
    }
}
