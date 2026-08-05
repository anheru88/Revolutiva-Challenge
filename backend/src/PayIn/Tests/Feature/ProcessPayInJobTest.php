<?php

declare(strict_types=1);

use Database\Seeders\PayInReferenceSeeder;
use Illuminate\Support\Facades\DB;
use Src\PayIn\Application\UseCase\ProcessPayInHandler;
use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\PayIn\Infrastructure\Laravel\Job\ProcessPayInJob;
use Src\PayIn\Tests\Support\PayInBuilder;
use Src\Shared\Domain\Exception\EntityNotFoundException;
use Src\Shared\Domain\ValueObject\Uuid;

beforeEach(function (): void {
    $this->seed(PayInReferenceSeeder::class);
});

it('processes a validated pay-in when the job is dispatched', function (): void {
    $payIn = PayInBuilder::validated();

    ProcessPayInJob::dispatch($payIn->uuid()->value());

    expect(DB::table('pay_ins')->where('uuid', $payIn->uuid()->value())->value('status'))
        ->toBe('PROCESSED');
});

it('records the failure when the provider declines from the job', function (): void {
    // provider_b rechaza importes por encima del límite simulado.
    $payIn = PayInBuilder::validated('provider_b', 2_000_000);

    ProcessPayInJob::dispatch($payIn->uuid()->value());

    expect(DB::table('pay_ins')->where('uuid', $payIn->uuid()->value())->value('status'))
        ->toBe('FAILED');
});

it('leaves a status history entry for the transition made by the job', function (): void {
    $payIn = PayInBuilder::validated();

    ProcessPayInJob::dispatch($payIn->uuid()->value());

    $payInId = DB::table('pay_ins')->where('uuid', $payIn->uuid()->value())->value('id');

    expect(DB::table('pay_in_status_history')->where('pay_in_id', $payInId)->pluck('current_status')->all())
        ->toBe(['CREATED', 'VALIDATED', 'PROCESSED']);
});

it('fails when the pay-in does not exist', function (): void {
    app(ProcessPayInHandler::class)->handle(new Uuid('99999999-9999-4999-8999-999999999999'));
})->throws(EntityNotFoundException::class);

it('fails when the provider behind the pay-in is gone', function (): void {
    $payIn = PayInBuilder::validated();

    // El proveedor deja de estar disponible entre el encolado y la ejecución
    // del job; el agregado ya persistido solo conserva su id interno. La clave
    // ajena impide borrar la fila, así que se sustituye el repositorio.
    $this->app->instance(PaymentProviderRepository::class, new class implements PaymentProviderRepository
    {
        public function findByCode(string $code): ?PaymentProvider
        {
            return null;
        }

        public function findById(int $id): ?PaymentProvider
        {
            return null;
        }
    });

    app(ProcessPayInHandler::class)->handle($payIn->uuid());
})->throws(EntityNotFoundException::class, 'PaymentProvider with id');
