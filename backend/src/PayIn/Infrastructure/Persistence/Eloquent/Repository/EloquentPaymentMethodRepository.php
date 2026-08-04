<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Repository;

use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Repository\PaymentMethodRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper\PaymentMethodMapper;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PaymentMethodModel;
use Src\Shared\Domain\ValueObject\Uuid;

final class EloquentPaymentMethodRepository implements PaymentMethodRepository
{
    public function findByUuid(Uuid $uuid): ?PaymentMethod
    {
        $model = PaymentMethodModel::query()->where('uuid', $uuid->value())->first();

        return $model !== null ? PaymentMethodMapper::toDomain($model) : null;
    }
}
