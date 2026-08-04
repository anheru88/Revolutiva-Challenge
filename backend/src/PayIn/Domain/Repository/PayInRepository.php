<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Repository;

use Src\PayIn\Domain\Entity\PayIn;
use Src\Shared\Domain\ValueObject\Uuid;

interface PayInRepository
{
    /**
     * Persiste el PayIn (insert o update) junto con sus transiciones de estado
     * pendientes en el historial. La operación debe ser atómica.
     */
    public function save(PayIn $payIn): void;

    public function findByUuid(Uuid $uuid): ?PayIn;
}
