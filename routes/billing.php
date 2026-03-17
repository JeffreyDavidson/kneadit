<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/plans', [BillingController::class, 'plans'])->name('plans');
    Route::post('/checkout/{plan}', [BillingController::class, 'checkout'])->name('checkout')->middleware('throttle:5,1');
    Route::get('/success', [BillingController::class, 'success'])->name('success');
    Route::get('/portal', [BillingController::class, 'portal'])->name('portal');
    Route::post('/swap/{plan}', [BillingController::class, 'swap'])->name('swap');
});

// Stripe webhooks (excluded from CSRF)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

// Stripe Connect webhooks (for connected account events)
Route::post('/stripe/connect-webhook', [\App\Http\Controllers\StripeConnectWebhookController::class, 'handle'])
    ->name('stripe.connect.webhook');
