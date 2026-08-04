<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObject;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Src\Shared\Domain\Exception\InvalidArgumentException;
use Stringable;

/**
 * Identificador público universal (UUID v4).
 */
final class Uuid implements Stringable
{
    public function __construct(private readonly string $value)
    {
        if (! RamseyUuid::isValid($value)) {
            throw new InvalidArgumentException(sprintf('<%s> does not allow the value <%s>.', self::class, $value));
        }
    }

    public static function random(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
