<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/plans', [BillingController::class, 'plans'])->name('plans');
    Route::post('/checkout/{plan}', [BillingController::class, 'checkout'])->name('checkout');
    Route::get('/success', [BillingController::class, 'success'])->name('success');
    Route::get('/portal', [BillingController::class, 'portal'])->name('portal');
    Route::post('/swap/{plan}', [BillingController::class, 'swap'])->name('swap');
});

// Stripe webhooks (excluded from CSRF)
Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');
