<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Repository;

use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Repository\PayInRepository;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Mapper\PayInMapper;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PayInModel;
use Src\PayIn\Infrastructure\Persistence\Eloquent\Model\PayInStatusHistoryModel;
use Src\Shared\Domain\ValueObject\Uuid;

final class EloquentPayInRepository implements PayInRepository
{
    /**
     * Persiste el PayIn (insert/update) y vuelca las transiciones de estado
     * pendientes al historial. La atomicidad la garantiza el TransactionManager
     * que envuelve la llamada (ver CreatePayInHandler y ADR-009).
     */
    public function save(PayIn $payIn): void
    {
        $model = $payIn->id() !== null
            ? PayInModel::query()->findOrFail($payIn->id())
            : new PayInModel;

        $model->fill([
            'uuid' => $payIn->uuid()->value(),
            'customer_id' => $payIn->customerId(),
            'account_id' => $payIn->accountId(),
            'payment_method_id' => $payIn->paymentMethodId(),
            'payment_provider_id' => $payIn->paymentProviderId(),
            'amount' => $payIn->amount()->amount(),
            'currency' => $payIn->amount()->currency(),
            'status' => $payIn->status()->value,
            'provider_request' => $payIn->providerRequest(),
            'provider_response' => $payIn->providerResponse(),
        ]);
        $model->save();

        if ($payIn->id() === null) {
            $payIn->assignId($model->id);
        }

        foreach ($payIn->pullRecordedTransitions() as $transition) {
            PayInStatusHistoryModel::query()->create([
                'pay_in_id' => $model->id,
                'previous_status' => $transition->previous?->value,
                'current_status' => $transition->current->value,
            ]);
        }
    }

    public function findByUuid(Uuid $uuid): ?PayIn
    {
        $model = PayInModel::query()->where('uuid', $uuid->value())->first();

        return $model !== null ? PayInMapper::toDomain($model) : null;
    }
}
