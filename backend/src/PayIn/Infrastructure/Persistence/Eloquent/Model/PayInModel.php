<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $customer_id
 * @property int $account_id
 * @property int $payment_method_id
 * @property int $payment_provider_id
 * @property int $amount
 * @property string $currency
 * @property string $status
 * @property array<string, mixed>|null $provider_request
 * @property array<string, mixed>|null $provider_response
 */
final class PayInModel extends Model
{
    protected $table = 'pay_ins';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'provider_request' => 'array',
        'provider_response' => 'array',
    ];
}
