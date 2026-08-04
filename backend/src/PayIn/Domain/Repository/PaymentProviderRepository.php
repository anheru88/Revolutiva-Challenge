<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Repository;

use Src\PayIn\Domain\Entity\PaymentProvider;

interface PaymentProviderRepository
{
    public function findByCode(string $code): ?PaymentProvider;

    /**
     * Búsqueda por identificador interno: la usa el procesamiento diferido,
     * que solo conserva los ids del agregado ya persistido (ADR-005).
     */
    public function findById(int $id): ?PaymentProvider;
}
