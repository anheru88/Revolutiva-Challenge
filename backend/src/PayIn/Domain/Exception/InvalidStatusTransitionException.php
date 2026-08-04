<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Exception;

use Src\PayIn\Domain\Enum\PayInStatus;
use Src\Shared\Domain\Exception\DomainException;

final class InvalidStatusTransitionException extends DomainException
{
    public static function between(PayInStatus $from, PayInStatus $to): self
    {
        return new self(sprintf(
            'Invalid PayIn status transition from [%s] to [%s].',
            $from->value,
            $to->value,
        ));
    }
}
