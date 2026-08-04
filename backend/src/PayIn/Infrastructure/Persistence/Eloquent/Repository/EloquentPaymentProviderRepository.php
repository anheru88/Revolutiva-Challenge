<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Repository;

use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\PayIn\Domain\Repository\PaymentProviderRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper\PaymentProviderMapper;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PaymentProviderModel;

final class EloquentPaymentProviderRepository implements PaymentProviderRepository
{
    public function findByCode(string $code): ?PaymentProvider
    {
        $model = PaymentProviderModel::query()->where('code', $code)->first();

        return $model !== null ? PaymentProviderMapper::toDomain($model) : null;
    }

    public function findById(int $id): ?PaymentProvider
    {
        $model = PaymentProviderModel::query()->find($id);

        return $model !== null ? PaymentProviderMapper::toDomain($model) : null;
    }
}
