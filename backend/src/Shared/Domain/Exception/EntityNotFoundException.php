<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exception;

/**
 * Se lanza cuando no se encuentra una entidad esperada
 * (customer, account, payment method, provider o pay-in).
 */
final class EntityNotFoundException extends DomainException
{
    public static function withUuid(string $entity, string $uuid): self
    {
        return new self(sprintf('%s with uuid [%s] was not found.', $entity, $uuid));
    }

    public static function withCode(string $entity, string $code): self
    {
        return new self(sprintf('%s with code [%s] was not found.', $entity, $code));
    }

    public static function withId(string $entity, int $id): self
    {
        return new self(sprintf('%s with id [%d] was not found.', $entity, $id));
    }
}
