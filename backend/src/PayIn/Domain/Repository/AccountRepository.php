<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Repository;

use Src\PayIn\Domain\Entity\Account;
use Src\Shared\Domain\ValueObject\Uuid;

interface AccountRepository
{
    public function findByUuid(Uuid $uuid): ?Account;
}
