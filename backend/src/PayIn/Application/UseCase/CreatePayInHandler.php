<?php

declare(strict_types=1);

namespace Src\PayIn\Application\UseCase;

use Src\PayIn\Application\Command\CreatePayInCommand;
use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Application\Query\PayInReadRepository;
use Src\PayIn\Application\Query\PayInResponse;
use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Exception\BusinessRuleViolationException;
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
 * → VALIDATED → procesamiento con el proveedor → PROCESSED/FAILED → persistencia
 * atómica con historial (ver diagrama de secuencia y ADR-004, ADR-009).
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

        // Se resuelve el adaptador antes de persistir para no dejar registros
        // huérfanos si el proveedor no tiene adaptador registrado.
        $adapter = $this->providerResolver->resolve($provider->code());

        // Estado CREATED → VALIDATED (reglas de negocio superadas).
        $payIn = PayIn::create(
            uuid: Uuid::random(),
            customerId: (int) $customer->id(),
            accountId: (int) $account->id(),
            paymentMethodId: (int) $paymentMethod->id(),
            paymentProviderId: (int) $provider->id(),
            amount: Money::of($command->amount, $command->currency),
        );
        $payIn->markValidated();

        // El orquestador PERSISTE en base de datos ANTES de enviar la transacción
        // al proveedor. Escritura atómica (PayIn + historial CREATED/VALIDATED).
        $this->transaction->transactional(function () use ($payIn): void {
            $this->payIns->save($payIn);
        });

        // Envío al proveedor, FUERA de la transacción (ADR-009).
        $result = $adapter->process($payIn);

        if ($result->successful) {
            $payIn->markProcessed($result->request, $result->response);
        } else {
            $payIn->markFailed($result->request, $result->response);
        }

        // Actualización atómica del estado final + historial (PROCESSED/FAILED).
        $this->transaction->transactional(function () use ($payIn): void {
            $this->payIns->save($payIn);
        });

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
