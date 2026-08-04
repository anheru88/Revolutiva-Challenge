<?php

use App\Providers\AppServiceProvider;

// Los providers del módulo (SharedServiceProvider, PayInServiceProvider) los
// registra el propio paquete `revolutiva/payin` vía package discovery
// (src/composer.json → extra.laravel.providers).
return [
    AppServiceProvider::class,
];
