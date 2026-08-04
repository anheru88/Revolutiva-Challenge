<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Entity;

use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Domain\Exception\InvalidStatusTransitionException;
use Src\PayIn\Domain\ValueObject\StatusTransition;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Raíz del agregado PayIn.
 *
 * Encapsula la máquina de estados y la lógica de negocio de la transacción.
 * No depende de Laravel ni de infraestructura. Las transiciones de estado se
 * registran internamente y las consume el repositorio para persistir el
 * historial (pay_in_status_history).
 */
final class PayIn
{
    /** @var array<int, StatusTransition> */
    private array $recordedTransitions = [];

    /**
     * @param  array<string, mixed>|null  $providerRequest
     * @param  array<string, mixed>|null  $providerResponse
     */
    private function __construct(
        private ?int $id,
        private readonly Uuid $uuid,
        private readonly int $customerId,
        private readonly int $accountId,
        private readonly int $paymentMethodId,
        private readonly int $paymentProviderId,
        private readonly Money $amount,
        private PayInStatus $status,
        private ?array $providerRequest = null,
        private ?array $providerResponse = null,
    ) {}

    /**
     * Crea una transacción nueva en estado CREATED.
     */
    public static function create(
        Uuid $uuid,
        int $customerId,
        int $accountId,
        int $paymentMethodId,
        int $paymentProviderId,
        Money $amount,
    ): self {
        $payIn = new self(
            id: null,
            uuid: $uuid,
            customerId: $customerId,
            accountId: $accountId,
            paymentMethodId: $paymentMethodId,
            paymentProviderId: $paymentProviderId,
            amount: $amount,
            status: PayInStatus::CREATED,
        );

        $payIn->recordedTransitions[] = new StatusTransition(null, PayInStatus::CREATED);

        return $payIn;
    }

    /**
     * Reconstituye la entidad desde persistencia (sin registrar transiciones).
     *
     * @param  array<string, mixed>|null  $providerRequest
     * @param  array<string, mixed>|null  $providerResponse
     */
    public static function reconstitute(
        int $id,
        Uuid $uuid,
        int $customerId,
        int $accountId,
        int $paymentMethodId,
        int $paymentProviderId,
        Money $amount,
        PayInStatus $status,
        ?array $providerRequest,
        ?array $providerResponse,
    ): self {
        return new self(
            id: $id,
            uuid: $uuid,
            customerId: $customerId,
            accountId: $accountId,
            paymentMethodId: $paymentMethodId,
            paymentProviderId: $paymentProviderId,
            amount: $amount,
            status: $status,
            providerRequest: $providerRequest,
            providerResponse: $providerResponse,
        );
    }

    public function markValidated(): void
    {
        $this->transitionTo(PayInStatus::VALIDATED);
    }

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public function markProcessed(array $request, array $response): void
    {
        $this->providerRequest = $request;
        $this->providerResponse = $response;
        $this->transitionTo(PayInStatus::PROCESSED);
    }

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public function markFailed(array $request, array $response): void
    {
        $this->providerRequest = $request;
        $this->providerResponse = $response;
        $this->transitionTo(PayInStatus::FAILED);
    }

    private function transitionTo(PayInStatus $target): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw InvalidStatusTransitionException::between($this->status, $target);
        }

        $this->recordedTransitions[] = new StatusTransition($this->status, $target);
        $this->status = $target;
    }

    /**
     * Devuelve las transiciones acumuladas y limpia el buffer interno.
     *
     * @return array<int, StatusTransition>
     */
    public function pullRecordedTransitions(): array
    {
        $transitions = $this->recordedTransitions;
        $this->recordedTransitions = [];

        return $transitions;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function accountId(): int
    {
        return $this->accountId;
    }

    public function paymentMethodId(): int
    {
        return $this->paymentMethodId;
    }

    public function paymentProviderId(): int
    {
        return $this->paymentProviderId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function status(): PayInStatus
    {
        return $this->status;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function providerRequest(): ?array
    {
        return $this->providerRequest;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function providerResponse(): ?array
    {
        return $this->providerResponse;
    }
}
