<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Repository;

use Src\PayIn\Domain\Entity\Customer;
use Src\Shared\Domain\ValueObject\Uuid;

interface CustomerRepository
{
    public function findByUuid(Uuid $uuid): ?Customer;
}
