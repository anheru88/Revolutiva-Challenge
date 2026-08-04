<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\PayIn\Infrastructure\Http\Controller\PayInController;

Route::prefix('v1')->group(function (): void {
    Route::post('/pay-ins', [PayInController::class, 'store']);
    Route::get('/pay-ins/{uuid}', [PayInController::class, 'show']);
});
