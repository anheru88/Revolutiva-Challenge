<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper;

use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PaymentMethodModel;
use Src\Shared\Domain\ValueObject\Uuid;

final class PaymentMethodMapper
{
    public static function toDomain(PaymentMethodModel $model): PaymentMethod
    {
        return new PaymentMethod(
            id: $model->id,
            uuid: new Uuid($model->uuid),
            accountId: $model->account_id,
            type: $model->type,
        );
    }
}
