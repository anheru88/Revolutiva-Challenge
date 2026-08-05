<?php

declare(strict_types=1);

use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Entity\Customer;
use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\Shared\Domain\ValueObject\Email;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

it('exposes customer attributes', function (): void {
    $uuid = Uuid::random();
    $customer = new Customer(1, $uuid, 'Acme Corp', new Email('billing@acme.test'));

    expect($customer->id())->toBe(1)
        ->and($customer->uuid()->equals($uuid))->toBeTrue()
        ->and($customer->name())->toBe('Acme Corp')
        ->and($customer->email()->value())->toBe('billing@acme.test');
});

it('knows whether an account belongs to a customer', function (): void {
    $uuid = Uuid::random();
    $account = new Account(5, $uuid, 1, 'ACC-0001');

    expect($account->id())->toBe(5)
        ->and($account->uuid()->equals($uuid))->toBeTrue()
        ->and($account->customerId())->toBe(1)
        ->and($account->accountNumber())->toBe('ACC-0001')
        ->and($account->belongsToCustomer(1))->toBeTrue()
        ->and($account->belongsToCustomer(99))->toBeFalse();
});

it('knows whether a payment method belongs to an account', function (): void {
    $uuid = Uuid::random();
    $method = new PaymentMethod(7, $uuid, 5, 'card');

    expect($method->id())->toBe(7)
        ->and($method->uuid()->equals($uuid))->toBeTrue()
        ->and($method->accountId())->toBe(5)
        ->and($method->type())->toBe('card')
        ->and($method->belongsToAccount(5))->toBeTrue()
        ->and($method->belongsToAccount(1))->toBeFalse();
});

it('exposes payment provider attributes', function (): void {
    $provider = new PaymentProvider(3, 'provider_a', 'Provider A');

    expect($provider->id())->toBe(3)
        ->and($provider->code())->toBe('provider_a')
        ->and($provider->name())->toBe('Provider A');
});

it('exposes stringable value objects', function (): void {
    $uuid = new Uuid('11111111-1111-4111-8111-111111111111');

    expect((string) $uuid)->toBe('11111111-1111-4111-8111-111111111111')
        ->and((string) new Email('a@b.test'))->toBe('a@b.test')
        ->and(new Email('a@b.test')->equals(new Email('a@b.test')))->toBeTrue()
        ->and(Money::of(100, 'EUR')->currency())->toBe('EUR');
});
