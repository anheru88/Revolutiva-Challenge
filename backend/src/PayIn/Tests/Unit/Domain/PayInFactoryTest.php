<?php

declare(strict_types=1);

use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Entity\Customer;
use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Domain\Factory\PayInFactory;
use Src\Shared\Domain\ValueObject\Email;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

it('assembles a CREATED pay-in from the resolved entities', function (): void {
    $customer = new Customer(1, Uuid::random(), 'Acme Corp', new Email('billing@acme.test'));
    $account = new Account(5, Uuid::random(), 1, 'ACC-0001');
    $paymentMethod = new PaymentMethod(7, Uuid::random(), 5, 'card');
    $provider = new PaymentProvider(3, 'provider_a', 'Provider A');
    $amount = Money::of(15000, 'USD');

    $payIn = (new PayInFactory)->forNewTransaction(
        customer: $customer,
        account: $account,
        paymentMethod: $paymentMethod,
        provider: $provider,
        amount: $amount,
    );

    expect($payIn->status())->toBe(PayInStatus::CREATED)
        ->and($payIn->customerId())->toBe(1)
        ->and($payIn->accountId())->toBe(5)
        ->and($payIn->paymentMethodId())->toBe(7)
        ->and($payIn->paymentProviderId())->toBe(3)
        ->and($payIn->amount()->equals($amount))->toBeTrue();
});

it('generates a distinct public uuid for every pay-in', function (): void {
    $factory = new PayInFactory;
    $customer = new Customer(1, Uuid::random(), 'Acme Corp', new Email('billing@acme.test'));
    $account = new Account(5, Uuid::random(), 1, 'ACC-0001');
    $paymentMethod = new PaymentMethod(7, Uuid::random(), 5, 'card');
    $provider = new PaymentProvider(3, 'provider_a', 'Provider A');

    $first = $factory->forNewTransaction($customer, $account, $paymentMethod, $provider, Money::of(100, 'USD'));
    $second = $factory->forNewTransaction($customer, $account, $paymentMethod, $provider, Money::of(100, 'USD'));

    expect($first->uuid()->equals($second->uuid()))->toBeFalse();
});
