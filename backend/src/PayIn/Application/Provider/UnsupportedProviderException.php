<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Provider;

use Src\Shared\Domain\Exception\InvalidArgumentException;

final class UnsupportedProviderException extends InvalidArgumentException
{
    public static function withCode(string $code): self
    {
        return new self(sprintf('No adapter is registered for payment provider [%s].', $code));
    }
}
