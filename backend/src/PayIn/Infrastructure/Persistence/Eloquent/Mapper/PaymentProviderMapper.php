<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper;

use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PaymentProviderModel;

final class PaymentProviderMapper
{
    public static function toDomain(PaymentProviderModel $model): PaymentProvider
    {
        return new PaymentProvider(
            id: $model->id,
            code: $model->code,
            name: $model->name,
        );
    }
}
