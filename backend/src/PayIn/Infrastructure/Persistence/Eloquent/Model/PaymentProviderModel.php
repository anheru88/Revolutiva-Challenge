<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
final class PaymentProviderModel extends Model
{
    protected $table = 'payment_providers';

    public $timestamps = false;

    protected $guarded = [];
}
