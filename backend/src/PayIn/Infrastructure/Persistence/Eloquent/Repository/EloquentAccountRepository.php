<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Repository;

use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Repository\AccountRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper\AccountMapper;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\AccountModel;
use Src\Shared\Domain\ValueObject\Uuid;

final class EloquentAccountRepository implements AccountRepository
{
    public function findByUuid(Uuid $uuid): ?Account
    {
        $model = AccountModel::query()->where('uuid', $uuid->value())->first();

        return $model !== null ? AccountMapper::toDomain($model) : null;
    }
}
