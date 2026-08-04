<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Shared\Tests\TestCase;

// Único archivo fuera de src/: Pest exige que su "test directory" exista y
// contenga el Pest.php de arranque (bin/pest lo busca en `tests` salvo que se
// pase --test-directory). Las pruebas viven en src/<Modulo>/Tests, así que aquí
// solo se enlaza la configuración, con rutas absolutas.
//
// Las de Feature usan el TestCase de Laravel y una base de datos fresca.
// Las de Unit (dominio/aplicación) son PHP puro y no arrancan el framework.
uses(TestCase::class, RefreshDatabase::class)->in(
    __DIR__.'/../src/PayIn/Tests/Feature',
);
