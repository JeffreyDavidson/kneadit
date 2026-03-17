<?php

use App\Http\Controllers\Billing\BillingPortalController;
use App\Http\Controllers\Billing\CheckoutController;
use App\Http\Controllers\Billing\CheckoutSuccessController;
use App\Http\Controllers\Billing\ShowPlansController;
use App\Http\Controllers\Billing\SwapPlanController;
use App\Http\Controllers\StripeConnectWebhookController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/plans', ShowPlansController::class)->name('plans');
    Route::post('/checkout/{plan}', CheckoutController::class)->name('checkout')->middleware('throttle:5,1');
    Route::get('/success', CheckoutSuccessController::class)->name('success');
    Route::get('/portal', BillingPortalController::class)->name('portal');
    Route::post('/swap/{plan}', SwapPlanController::class)->name('swap');
});

// Stripe webhooks (excluded from CSRF)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

// Stripe Connect webhooks (for connected account events)
Route::post('/stripe/connect-webhook', StripeConnectWebhookController::class)
    ->name('stripe.connect.webhook');
