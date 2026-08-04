<?php

declare(strict_types=1);

namespace Src\PayIn\Application\UseCase;

use Src\PayIn\Application\Command\CreatePayInCommand;
use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Application\Query\PayInReadRepository;
use Src\PayIn\Application\Query\PayInResponse;
use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Exception\BusinessRuleViolationException;
use Src\PayIn\Domain\Factory\PayInFactory;
use Src\PayIn\Domain\Repository\AccountRepository;
use Src\PayIn\Domain\Repository\CustomerRepository;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Domain\Repository\PaymentMethodRepository;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\Shared\Application\TransactionManager;
use Src\Shared\Domain\Exception\EntityNotFoundException;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Orquesta la creación de un PayIn:
 * validación de request (previa, en HTTP) → reglas de negocio → estado CREATED
 * → VALIDATED → persistencia atómica → procesamiento con el proveedor
 * (delegado en ProcessPayInHandler) → PROCESSED/FAILED
 * (ver diagrama de secuencia y ADR-004, ADR-009).
 */
final class CreatePayInHandler
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly AccountRepository $accounts,
        private readonly PaymentMethodRepository $paymentMethods,
        private readonly PaymentProviderRepository $providers,
        private readonly PayInRepository $payIns,
        private readonly PayInReadRepository $payInReader,
        private readonly ProviderResolver $providerResolver,
        private readonly PayInFactory $payInFactory,
        private readonly ProcessPayInHandler $processPayIn,
        private readonly TransactionManager $transaction,
    ) {}

    public function handle(CreatePayInCommand $command): PayInResponse
    {
        $customer = $this->customers->findByUuid(new Uuid($command->customerUuid))
            ?? throw EntityNotFoundException::withUuid('Customer', $command->customerUuid);

        $account = $this->accounts->findByUuid(new Uuid($command->accountUuid))
            ?? throw EntityNotFoundException::withUuid('Account', $command->accountUuid);

        $paymentMethod = $this->paymentMethods->findByUuid(new Uuid($command->paymentMethodUuid))
            ?? throw EntityNotFoundException::withUuid('PaymentMethod', $command->paymentMethodUuid);

        $provider = $this->providers->findByCode($command->providerCode)
            ?? throw EntityNotFoundException::withCode('PaymentProvider', $command->providerCode);

        $this->assertBusinessRules($customer->id(), $account, $paymentMethod);

        // Se comprueba que el proveedor tenga adaptador antes de persistir, para
        // no dejar registros huérfanos si no lo tiene. El adaptador lo resuelve
        // después ProcessPayInHandler, que es quien lo usa.
        $this->providerResolver->resolve($provider->code());

        // Estado CREATED (lo ensambla la factory) → VALIDATED (reglas superadas).
        $payIn = $this->payInFactory->forNewTransaction(
            customer: $customer,
            account: $account,
            paymentMethod: $paymentMethod,
            provider: $provider,
            amount: Money::of($command->amount, $command->currency),
        );
        $payIn->markValidated();

        // El orquestador PERSISTE en base de datos ANTES de enviar la transacción
        // al proveedor. Escritura atómica (PayIn + historial CREATED/VALIDATED).
        $this->transaction->transactional(function () use ($payIn): void {
            $this->payIns->save($payIn);
        });

        // Procesamiento con el proveedor. Es síncrono (ADR-004): se ejecuta en
        // línea dentro del request. Para pasarlo a cola basta sustituir esta
        // llamada por ProcessPayInJob::dispatch($payIn->uuid()->value()); el
        // caso de uso invocado es el mismo.
        $this->processPayIn->handle($payIn->uuid());

        return $this->payInReader->findByUuid($payIn->uuid())
            ?? throw EntityNotFoundException::withUuid('PayIn', $payIn->uuid()->value());
    }

    private function assertBusinessRules(?int $customerId, Account $account, PaymentMethod $paymentMethod): void
    {
        if (! $account->belongsToCustomer((int) $customerId)) {
            throw new BusinessRuleViolationException('The account does not belong to the given customer.');
        }

        if (! $paymentMethod->belongsToAccount((int) $account->id())) {
            throw new BusinessRuleViolationException('The payment method does not belong to the given account.');
        }
    }
}
