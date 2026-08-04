<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Entity\Customer;
use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\Shared\Domain\ValueObject\Email;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

final class EntitiesTest extends TestCase
{
    public function test_customer_exposes_its_attributes(): void
    {
        $uuid = Uuid::random();
        $customer = new Customer(1, $uuid, 'Acme Corp', new Email('billing@acme.test'));

        $this->assertSame(1, $customer->id());
        $this->assertTrue($customer->uuid()->equals($uuid));
        $this->assertSame('Acme Corp', $customer->name());
        $this->assertSame('billing@acme.test', $customer->email()->value());
    }

    public function test_account_belongs_to_customer(): void
    {
        $account = new Account(5, Uuid::random(), 1, 'ACC-0001');

        $this->assertSame(5, $account->id());
        $this->assertSame(1, $account->customerId());
        $this->assertSame('ACC-0001', $account->accountNumber());
        $this->assertTrue($account->belongsToCustomer(1));
        $this->assertFalse($account->belongsToCustomer(99));
    }

    public function test_payment_method_belongs_to_account(): void
    {
        $method = new PaymentMethod(7, Uuid::random(), 5, 'card');

        $this->assertSame(7, $method->id());
        $this->assertSame(5, $method->accountId());
        $this->assertSame('card', $method->type());
        $this->assertTrue($method->belongsToAccount(5));
        $this->assertFalse($method->belongsToAccount(1));
    }

    public function test_payment_provider_exposes_its_attributes(): void
    {
        $provider = new PaymentProvider(3, 'provider_a', 'Provider A');

        $this->assertSame(3, $provider->id());
        $this->assertSame('provider_a', $provider->code());
        $this->assertSame('Provider A', $provider->name());
    }

    public function test_value_objects_are_stringable(): void
    {
        $uuid = new Uuid('11111111-1111-4111-8111-111111111111');

        $this->assertSame('11111111-1111-4111-8111-111111111111', (string) $uuid);
        $this->assertSame('a@b.test', (string) new Email('a@b.test'));
        $this->assertTrue(new Email('a@b.test')->equals(new Email('a@b.test')));
        $this->assertSame('EUR', Money::of(100, 'EUR')->currency());
    }
}
