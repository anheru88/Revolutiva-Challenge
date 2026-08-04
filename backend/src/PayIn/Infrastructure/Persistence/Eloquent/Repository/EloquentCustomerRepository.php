<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Repository;

use Src\PayIn\Domain\Entity\Customer;
use Src\PayIn\Domain\Repository\CustomerRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper\CustomerMapper;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\CustomerModel;
use Src\Shared\Domain\ValueObject\Uuid;

final class EloquentCustomerRepository implements CustomerRepository
{
    public function findByUuid(Uuid $uuid): ?Customer
    {
        $model = CustomerModel::query()->where('uuid', $uuid->value())->first();

        return $model !== null ? CustomerMapper::toDomain($model) : null;
    }
}
