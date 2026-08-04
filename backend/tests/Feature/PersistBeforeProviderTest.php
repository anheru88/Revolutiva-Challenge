<?php

declare(strict_types=1);

use Database\Seeders\PayInReferenceSeeder;
use Illuminate\Support\Facades\DB;
use Src\PayIn\Application\Command\CreatePayInCommand;
use Src\PayIn\Application\Provider\PaymentProviderPort;
use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Application\Provider\ProviderResult;
use Src\PayIn\Application\UseCase\CreatePayInHandler;
use Src\PayIn\Domain\Entity\PayIn;

beforeEach(function (): void {
    $this->seed(PayInReferenceSeeder::class);
});

it('persists the pay-in in the database before sending it to the provider', function (): void {
    DB::table('payment_providers')->insert(['code' => 'provider_spy', 'name' => 'Spy']);

    // Adaptador espía: al ser invocado, lee el estado del PayIn ya persistido.
    $spy = new class implements PaymentProviderPort
    {
        public ?string $statusWhenCalled = 'NOT_CALLED';

        public function code(): string
        {
            return 'provider_spy';
        }

        public function process(PayIn $payIn): ProviderResult
        {
            $this->statusWhenCalled = DB::table('pay_ins')
                ->where('uuid', $payIn->uuid()->value())
                ->value('status');

            return ProviderResult::success(['sent' => true], ['status' => 'approved']);
        }
    };

    $this->app->instance(ProviderResolver::class, new ProviderResolver([$spy]));

    $response = app(CreatePayInHandler::class)->handle(new CreatePayInCommand(
        customerUuid: PayInReferenceSeeder::CUSTOMER_UUID,
        accountUuid: PayInReferenceSeeder::ACCOUNT_UUID,
        paymentMethodUuid: PayInReferenceSeeder::PAYMENT_METHOD_UUID,
        providerCode: 'provider_spy',
        amount: 15000,
        currency: 'USD',
    ));

    // En el momento de enviar al proveedor, el PayIn ya estaba en BD como VALIDATED.
    expect($spy->statusWhenCalled)->toBe('VALIDATED')
        ->and($response->status)->toBe('PROCESSED');
});
