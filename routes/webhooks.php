<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/mpesa', [WebhookController::class, 'mpesa'])->name('mpesa');
    Route::match(['get', 'post'], '/pesapal', [WebhookController::class, 'pesapal'])->name('pesapal');
});
