<?php

use App\Providers\AppServiceProvider;
use Src\PayIn\Infrastructure\Laravel\PayInServiceProvider;

return [
    AppServiceProvider::class,
    PayInServiceProvider::class,
];
