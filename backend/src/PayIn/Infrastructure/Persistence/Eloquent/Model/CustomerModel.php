<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 */
final class CustomerModel extends Model
{
    protected $table = 'customers';

    public $timestamps = false;

    protected $guarded = [];
}
