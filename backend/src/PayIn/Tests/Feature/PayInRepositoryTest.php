<?php

declare(strict_types=1);

use Database\Seeders\PayInReferenceSeeder;
use Src\PayIn\Application\Command\CreatePayInCommand;
use Src\PayIn\Application\Query\PayInReadRepository;
use Src\PayIn\Application\UseCase\CreatePayInHandler;
use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Tests\Support\PayInBuilder;
use Src\Shared\Domain\ValueObject\Uuid;

beforeEach(function (): void {
    $this->seed(PayInReferenceSeeder::class);
});

it('reconstitutes a persisted pay-in as a domain entity', function (): void {
    $response = app(CreatePayInHandler::class)->handle(new CreatePayInCommand(
        customerUuid: PayInReferenceSeeder::CUSTOMER_UUID,
        accountUuid: PayInReferenceSeeder::ACCOUNT_UUID,
        paymentMethodUuid: PayInReferenceSeeder::PAYMENT_METHOD_UUID,
        providerCode: 'provider_a',
        amount: 15000,
        currency: 'USD',
    ));

    $payIn = app(PayInRepository::class)->findByUuid(new Uuid($response->uuid));

    expect($payIn)->not->toBeNull()
        ->and($payIn->uuid()->value())->toBe($response->uuid)
        ->and($payIn->status())->toBe(PayInStatus::PROCESSED)
        ->and($payIn->amount()->amount())->toBe(15000)
        ->and($payIn->amount()->currency())->toBe('USD')
        ->and($payIn->providerResponse())->toBeArray();
});

it('returns null for an unknown pay-in', function (): void {
    $payIn = app(PayInRepository::class)
        ->findByUuid(new Uuid('99999999-9999-4999-8999-999999999999'));

    expect($payIn)->toBeNull();
});

it('reads a pay-in that has not reached the provider yet', function (): void {
    // Antes del paso de proveedor, provider_request y provider_response son
    // NULL en base de datos: el modelo de lectura no debe inventar arrays.
    $payIn = PayInBuilder::validated();

    $response = app(PayInReadRepository::class)->findByUuid($payIn->uuid());

    expect($response)->not->toBeNull()
        ->and($response->status)->toBe('VALIDATED')
        ->and($response->providerRequest)->toBeNull()
        ->and($response->providerResponse)->toBeNull();
});
