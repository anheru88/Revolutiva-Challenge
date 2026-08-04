<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Repository;

use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\Shared\Domain\ValueObject\Uuid;

interface PaymentMethodRepository
{
    public function findByUuid(Uuid $uuid): ?PaymentMethod;
}
