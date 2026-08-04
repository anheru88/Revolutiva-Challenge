<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Src\Shared\Application\TransactionManager;

final class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TransactionManager::class,
            static fn (Application $app): LaravelTransactionManager => new LaravelTransactionManager(
                $app->make('db')->connection(),
            ),
        );
    }

    public function boot(): void
    {
        // Las migraciones de las tablas de infraestructura (users, cache, jobs)
        // viven con el código compartido, no en database/migrations.
        $this->loadMigrationsFrom(__DIR__.'/../Persistence/Migrations');
    }
}
