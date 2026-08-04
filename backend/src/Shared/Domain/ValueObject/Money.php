<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObject;

use Src\Shared\Domain\Exception\InvalidArgumentException;

/**
 * Importe monetario.
 *
 * El monto se almacena en la unidad menor de la moneda (por ejemplo, centavos)
 * como entero, evitando errores de punto flotante. La moneda es un código
 * ISO 4217 de tres letras.
 */
final class Money
{
    private const CURRENCY_PATTERN = '/^[A-Z]{3}$/';

    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        if (preg_match(self::CURRENCY_PATTERN, $currency) !== 1) {
            throw new InvalidArgumentException(sprintf('<%s> is not a valid ISO 4217 currency code.', $currency));
        }
    }

    public static function of(int $amount, string $currency): self
    {
        return new self($amount, strtoupper($currency));
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}
