<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper;

use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\AccountModel;
use Src\Shared\Domain\ValueObject\Uuid;

final class AccountMapper
{
    public static function toDomain(AccountModel $model): Account
    {
        return new Account(
            id: $model->id,
            uuid: new Uuid($model->uuid),
            customerId: $model->customer_id,
            accountNumber: $model->account_number,
        );
    }
}
