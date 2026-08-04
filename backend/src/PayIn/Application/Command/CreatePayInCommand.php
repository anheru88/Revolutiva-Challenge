<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Command;

/**
 * DTO de entrada para el caso de uso CreatePayIn.
 * Transporta datos primitivos ya validados por la capa HTTP.
 */
final class CreatePayInCommand
{
    public function __construct(
        public readonly string $customerUuid,
        public readonly string $accountUuid,
        public readonly string $paymentMethodUuid,
        public readonly string $providerCode,
        public readonly int $amount,
        public readonly string $currency,
    ) {}
}
