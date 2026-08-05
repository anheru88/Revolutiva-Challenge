<?php

declare(strict_types=1);

namespace Src\PayIn\Tests\Support;

use Database\Seeders\PayInReferenceSeeder;
use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Factory\PayInFactory;
use Src\PayIn\Domain\Repository\AccountRepository;
use Src\PayIn\Domain\Repository\CustomerRepository;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Domain\Repository\PaymentMethodRepository;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Ayudante de pruebas: deja un PayIn persistido en VALIDATED sobre los datos de
 * referencia del seeder, es decir, en el punto exacto en el que
 * CreatePayInHandler lo entrega al paso de proveedor.
 */
final class PayInBuilder
{
    public static function validated(string $providerCode = 'provider_a', int $amount = 15000): PayIn
    {
        $customer = app(CustomerRepository::class)->findByUuid(new Uuid(PayInReferenceSeeder::CUSTOMER_UUID));
        $account = app(AccountRepository::class)->findByUuid(new Uuid(PayInReferenceSeeder::ACCOUNT_UUID));
        $paymentMethod = app(PaymentMethodRepository::class)->findByUuid(new Uuid(PayInReferenceSeeder::PAYMENT_METHOD_UUID));
        $provider = app(PaymentProviderRepository::class)->findByCode($providerCode);

        $payIn = (new PayInFactory)->forNewTransaction(
            customer: $customer,
            account: $account,
            paymentMethod: $paymentMethod,
            provider: $provider,
            amount: Money::of($amount, 'USD'),
        );
        $payIn->markValidated();

        app(PayInRepository::class)->save($payIn);

        return $payIn;
    }
}
