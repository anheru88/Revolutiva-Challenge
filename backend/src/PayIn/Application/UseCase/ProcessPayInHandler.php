<?php

declare(strict_types=1);

namespace Src\PayIn\Application\UseCase;

use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\Shared\Application\TransactionManager;
use Src\Shared\Domain\Exception\EntityNotFoundException;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Envía un PayIn ya persistido al proveedor y registra el resultado.
 *
 * Es el paso que ADR-004 identifica como candidato a ejecutarse fuera del
 * request: recibe un UUID (no un objeto en memoria), recarga el agregado y no
 * comparte estado con quien lo invoca. Por eso sirve igual llamado en línea
 * desde CreatePayInHandler que despachado en cola por ProcessPayInJob; pasar a
 * asíncrono es cambiar la llamada, no el dominio.
 */
final class ProcessPayInHandler
{
    public function __construct(
        private readonly PayInRepository $payIns,
        private readonly PaymentProviderRepository $providers,
        private readonly ProviderResolver $providerResolver,
        private readonly TransactionManager $transaction,
    ) {}

    public function handle(Uuid $payInUuid): void
    {
        $payIn = $this->payIns->findByUuid($payInUuid)
            ?? throw EntityNotFoundException::withUuid('PayIn', $payInUuid->value());

        $provider = $this->providers->findById($payIn->paymentProviderId())
            ?? throw EntityNotFoundException::withId('PaymentProvider', $payIn->paymentProviderId());

        $adapter = $this->providerResolver->resolve($provider->code());

        // Llamada externa FUERA de cualquier transacción (ADR-009).
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
    }
}
