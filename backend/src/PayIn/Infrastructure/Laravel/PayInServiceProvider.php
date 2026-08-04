<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Application\Query\PayInReadRepository;
use Src\PayIn\Domain\Repository\AccountRepository;
use Src\PayIn\Domain\Repository\CustomerRepository;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Domain\Repository\PaymentMethodRepository;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Repository\EloquentAccountRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Repository\EloquentCustomerRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Repository\EloquentPayInReadRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Repository\EloquentPayInRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Repository\EloquentPaymentMethodRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Repository\EloquentPaymentProviderRepository;
use Src\PayIn\Infrastructure\Provider\FakeProviderAAdapter;
use Src\PayIn\Infrastructure\Provider\FakeProviderBAdapter;
use Src\Shared\Application\TransactionManager;
use Src\Shared\Infrastructure\Laravel\LaravelTransactionManager;

final class PayInServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private const REPOSITORY_BINDINGS = [
        CustomerRepository::class => EloquentCustomerRepository::class,
        AccountRepository::class => EloquentAccountRepository::class,
        PaymentMethodRepository::class => EloquentPaymentMethodRepository::class,
        PaymentProviderRepository::class => EloquentPaymentProviderRepository::class,
        PayInRepository::class => EloquentPayInRepository::class,
        PayInReadRepository::class => EloquentPayInReadRepository::class,
    ];

    public function register(): void
    {
        foreach (self::REPOSITORY_BINDINGS as $port => $adapter) {
            $this->app->bind($port, $adapter);
        }

        $this->app->bind(
            TransactionManager::class,
            static fn (Application $app): LaravelTransactionManager => new LaravelTransactionManager(
                $app->make('db')->connection(),
            ),
        );

        $this->app->singleton(ProviderResolver::class, static function (Application $app): ProviderResolver {
            return new ProviderResolver([
                $app->make(FakeProviderAAdapter::class),
                $app->make(FakeProviderBAdapter::class),
            ]);
        });
    }
}
