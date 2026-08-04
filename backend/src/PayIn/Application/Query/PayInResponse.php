<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Query;

/**
 * Modelo de lectura del PayIn expuesto por la API. Contiene los identificadores
 * públicos (uuid / code) de las entidades relacionadas y evita filtrar ids
 * internos (ver ADR-005).
 */
final class PayInResponse
{
    /**
     * @param  array<string, mixed>|null  $providerRequest
     * @param  array<string, mixed>|null  $providerResponse
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $customerUuid,
        public readonly string $accountUuid,
        public readonly string $paymentMethodUuid,
        public readonly string $providerCode,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $status,
        public readonly ?array $providerRequest,
        public readonly ?array $providerResponse,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'customer_uuid' => $this->customerUuid,
            'account_uuid' => $this->accountUuid,
            'payment_method_uuid' => $this->paymentMethodUuid,
            'provider_code' => $this->providerCode,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'provider_request' => $this->providerRequest,
            'provider_response' => $this->providerResponse,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
