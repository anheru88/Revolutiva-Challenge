<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Repository;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Src\PayIn\Application\Query\PayInReadRepository;
use Src\PayIn\Application\Query\PayInResponse;
use Src\Shared\Domain\ValueObject\Uuid;

final class EloquentPayInReadRepository implements PayInReadRepository
{
    public function findByUuid(Uuid $uuid): ?PayInResponse
    {
        $row = DB::table('pay_ins as p')
            ->join('customers as c', 'c.id', '=', 'p.customer_id')
            ->join('accounts as a', 'a.id', '=', 'p.account_id')
            ->join('payment_methods as pm', 'pm.id', '=', 'p.payment_method_id')
            ->join('payment_providers as pp', 'pp.id', '=', 'p.payment_provider_id')
            ->where('p.uuid', $uuid->value())
            ->select([
                'p.uuid as uuid',
                'c.uuid as customer_uuid',
                'a.uuid as account_uuid',
                'pm.uuid as payment_method_uuid',
                'pp.code as provider_code',
                'p.amount as amount',
                'p.currency as currency',
                'p.status as status',
                'p.provider_request as provider_request',
                'p.provider_response as provider_response',
                'p.created_at as created_at',
                'p.updated_at as updated_at',
            ])
            ->first();

        if ($row === null) {
            return null;
        }

        return new PayInResponse(
            uuid: $row->uuid,
            customerUuid: $row->customer_uuid,
            accountUuid: $row->account_uuid,
            paymentMethodUuid: $row->payment_method_uuid,
            providerCode: $row->provider_code,
            amount: (int) $row->amount,
            currency: $row->currency,
            status: $row->status,
            providerRequest: $this->decode($row->provider_request),
            providerResponse: $this->decode($row->provider_response),
            createdAt: Carbon::parse($row->created_at)->toIso8601String(),
            updatedAt: Carbon::parse($row->updated_at)->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(?string $json): ?array
    {
        if ($json === null) {
            return null;
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($json, true);

        return $decoded;
    }
}
