<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Las pruebas de Feature usan el TestCase de Laravel y una base de datos fresca.
// Las de Unit (dominio/aplicación) son PHP puro y no arrancan el framework.
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
