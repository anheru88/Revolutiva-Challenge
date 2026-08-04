<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property int $customer_id
 * @property string $account_number
 */
final class AccountModel extends Model
{
    protected $table = 'accounts';

    public $timestamps = false;

    protected $guarded = [];
}
