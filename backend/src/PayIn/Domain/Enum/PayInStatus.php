<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Enum;

/**
 * Estados del ciclo de vida de un PayIn.
 *
 * Transiciones válidas (ver ADR-007):
 *   CREATED   -> VALIDATED, FAILED
 *   VALIDATED -> PROCESSED, FAILED
 *   PROCESSED -> (terminal)
 *   FAILED    -> (terminal)
 */
enum PayInStatus: string
{
    case CREATED = 'CREATED';
    case VALIDATED = 'VALIDATED';
    case PROCESSED = 'PROCESSED';
    case FAILED = 'FAILED';

    /**
     * @return array<int, PayInStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::CREATED => [self::VALIDATED, self::FAILED],
            self::VALIDATED => [self::PROCESSED, self::FAILED],
            self::PROCESSED, self::FAILED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
