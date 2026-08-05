<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/**
 * Scramble restringe la documentación al entorno `local`; AppServiceProvider
 * define el gate `viewApiDocs` para exponerla también fuera de local, que es
 * como corre el contenedor (APP_ENV=production). Sin ese gate, el enlace de
 * Swagger UI que anuncia el README devolvería 403.
 */
it('serves the OpenAPI spec outside the local environment', function (): void {
    $this->getJson('/docs/api.json')
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0')
        ->assertJsonStructure(['paths' => ['/v1/pay-ins', '/v1/pay-ins/{uuid}']]);
});

it('serves the Swagger UI page', function (): void {
    $this->get('/docs/api')->assertOk();
});

it('keeps the sessions table usable by the database session driver', function (): void {
    // El stack de Docker corre con SESSION_DRIVER=database y las rutas web
    // (Swagger UI) abren sesión. La tabla debe tener las columnas que escribe
    // el handler de Laravel — user_id incluida, aunque no haya usuarios.
    config(['session.driver' => 'database']);

    $this->get('/docs/api')->assertOk();

    expect(DB::table('sessions')->count())->toBe(1);
});
