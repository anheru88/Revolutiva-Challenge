<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\ValueObject;

use Src\PayIn\Domain\Enum\PayInStatus;

/**
 * Registra una transición de estado del PayIn para su posterior
 * persistencia en el historial (pay_in_status_history).
 *
 * `previous` es null cuando corresponde a la creación (estado inicial).
 */
final class StatusTransition
{
    public function __construct(
        public readonly ?PayInStatus $previous,
        public readonly PayInStatus $current,
    ) {}
}
