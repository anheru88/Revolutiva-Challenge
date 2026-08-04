<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\PayInReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\PayIn\Application\Command\CreatePayInCommand;
use Src\PayIn\Application\UseCase\CreatePayInHandler;
use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\Shared\Domain\ValueObject\Uuid;
use Tests\TestCase;

final class PayInRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayInReferenceSeeder::class);
    }

    public function test_it_reconstitutes_a_persisted_pay_in_as_a_domain_entity(): void
    {
        $response = app(CreatePayInHandler::class)->handle(new CreatePayInCommand(
            customerUuid: PayInReferenceSeeder::CUSTOMER_UUID,
            accountUuid: PayInReferenceSeeder::ACCOUNT_UUID,
            paymentMethodUuid: PayInReferenceSeeder::PAYMENT_METHOD_UUID,
            providerCode: 'provider_a',
            amount: 15000,
            currency: 'USD',
        ));

        $payIn = app(PayInRepository::class)->findByUuid(new Uuid($response->uuid));

        $this->assertNotNull($payIn);
        $this->assertSame($response->uuid, $payIn->uuid()->value());
        $this->assertSame(PayInStatus::PROCESSED, $payIn->status());
        $this->assertSame(15000, $payIn->amount()->amount());
        $this->assertSame('USD', $payIn->amount()->currency());
        $this->assertIsArray($payIn->providerResponse());
    }

    public function test_it_returns_null_for_an_unknown_pay_in(): void
    {
        $payIn = app(PayInRepository::class)
            ->findByUuid(new Uuid('99999999-9999-4999-8999-999999999999'));

        $this->assertNull($payIn);
    }
}
