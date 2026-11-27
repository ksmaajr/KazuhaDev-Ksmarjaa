<?php

use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\SumoPodWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/payments/sumopod/{mode}', SumoPodWebhookController::class)->where('mode', 'sandbox|production')->middleware('throttle:120,1')->name('payments.webhook');

Route::prefix('v1')->middleware('throttle:license-api')->group(function (): void {
    Route::post('/licenses/validate', [LicenseController::class, 'validateKey'])->name('api.validate');
    Route::get('/games/dll', [LicenseController::class, 'dll'])->name('api.dll');
    Route::get('/licenses/status', [LicenseController::class, 'status'])->name('api.status');
});
