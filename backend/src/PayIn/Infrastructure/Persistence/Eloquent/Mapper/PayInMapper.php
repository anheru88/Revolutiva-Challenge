<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper;

use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PayInModel;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

final class PayInMapper
{
    public static function toDomain(PayInModel $model): PayIn
    {
        return PayIn::reconstitute(
            id: $model->id,
            uuid: new Uuid($model->uuid),
            customerId: $model->customer_id,
            accountId: $model->account_id,
            paymentMethodId: $model->payment_method_id,
            paymentProviderId: $model->payment_provider_id,
            amount: Money::of($model->amount, $model->currency),
            status: PayInStatus::from($model->status),
            providerRequest: $model->provider_request,
            providerResponse: $model->provider_response,
        );
    }
}
