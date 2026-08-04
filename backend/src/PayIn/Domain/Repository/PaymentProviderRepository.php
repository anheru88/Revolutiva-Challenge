<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Repository;

use Src\PayIn\Domain\Entity\PaymentProvider;

interface PaymentProviderRepository
{
    public function findByCode(string $code): ?PaymentProvider;
}
