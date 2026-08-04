<?php

declare(strict_types=1);

use Database\Seeders\PayInReferenceSeeder;
use Illuminate\Support\Facades\DB;
use Src\PayIn\Application\UseCase\ProcessPayInHandler;
use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Factory\PayInFactory;
use Src\PayIn\Domain\Repository\AccountRepository;
use Src\PayIn\Domain\Repository\CustomerRepository;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Domain\Repository\PaymentMethodRepository;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\PayIn\Infrastructure\Laravel\Job\ProcessPayInJob;
use Src\Shared\Domain\Exception\EntityNotFoundException;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

beforeEach(function (): void {
    $this->seed(PayInReferenceSeeder::class);
});

/**
 * Deja un PayIn persistido en VALIDATED, tal como lo hace CreatePayInHandler
 * justo antes del paso de proveedor.
 */
function validatedPayIn(string $providerCode = 'provider_a', int $amount = 15000): PayIn
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

it('processes a validated pay-in when the job is dispatched', function (): void {
    $payIn = validatedPayIn();

    ProcessPayInJob::dispatch($payIn->uuid()->value());

    expect(DB::table('pay_ins')->where('uuid', $payIn->uuid()->value())->value('status'))
        ->toBe('PROCESSED');
});

it('records the failure when the provider declines from the job', function (): void {
    // provider_b rechaza importes por encima del límite simulado.
    $payIn = validatedPayIn('provider_b', 2_000_000);

    ProcessPayInJob::dispatch($payIn->uuid()->value());

    expect(DB::table('pay_ins')->where('uuid', $payIn->uuid()->value())->value('status'))
        ->toBe('FAILED');
});

it('leaves a status history entry for the transition made by the job', function (): void {
    $payIn = validatedPayIn();

    ProcessPayInJob::dispatch($payIn->uuid()->value());

    $payInId = DB::table('pay_ins')->where('uuid', $payIn->uuid()->value())->value('id');

    expect(DB::table('pay_in_status_history')->where('pay_in_id', $payInId)->pluck('current_status')->all())
        ->toBe(['CREATED', 'VALIDATED', 'PROCESSED']);
});

it('fails when the pay-in does not exist', function (): void {
    app(ProcessPayInHandler::class)->handle(new Uuid('99999999-9999-4999-8999-999999999999'));
})->throws(EntityNotFoundException::class);
