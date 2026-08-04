<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pay_in_id
 * @property string|null $previous_status
 * @property string $current_status
 */
final class PayInStatusHistoryModel extends Model
{
    protected $table = 'pay_in_status_history';

    public const UPDATED_AT = null;

    protected $guarded = [];
}
