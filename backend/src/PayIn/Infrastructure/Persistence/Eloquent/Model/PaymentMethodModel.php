<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $account_id
 * @property string $type
 */
final class PaymentMethodModel extends Model
{
    protected $table = 'payment_methods';

    public $timestamps = false;

    protected $guarded = [];
}
