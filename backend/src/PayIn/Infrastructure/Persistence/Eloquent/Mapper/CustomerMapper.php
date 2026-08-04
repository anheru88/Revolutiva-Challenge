<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper;

use Src\PayIn\Domain\Entity\Customer;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\CustomerModel;
use Src\Shared\Domain\ValueObject\Email;
use Src\Shared\Domain\ValueObject\Uuid;

final class CustomerMapper
{
    public static function toDomain(CustomerModel $model): Customer
    {
        return new Customer(
            id: $model->id,
            uuid: new Uuid($model->uuid),
            name: $model->name,
            email: new Email($model->email),
        );
    }
}
