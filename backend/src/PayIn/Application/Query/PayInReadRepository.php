<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Query;

use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Puerto de lectura del PayIn. Devuelve un modelo de lectura enriquecido con los
 * identificadores públicos de las entidades relacionadas.
 */
interface PayInReadRepository
{
    public function findByUuid(Uuid $uuid): ?PayInResponse;
}
