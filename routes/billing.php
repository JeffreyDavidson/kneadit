<?php

use App\Http\Controllers\Billing\BillingPortalController;
use App\Http\Controllers\Billing\CheckoutController;
use App\Http\Controllers\Billing\CheckoutSuccessController;
use App\Http\Controllers\Billing\ConsumeBillingHandoffController;
use App\Http\Controllers\Billing\ShowPlansController;
use App\Http\Controllers\Billing\SwapPlanController;
use App\Http\Controllers\Stripe\StripeConnectWebhookController;
use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Http\Middleware\PreventAccessFromTenantDomains;
use App\Http\Middleware\RequireStripeWebhookSecret;
use Illuminate\Support\Facades\Route;

// Central only: a bakery host answers 404 here instead of failing on the central database.
Route::middleware(['web', PreventAccessFromTenantDomains::class])->prefix('billing')->name('billing.')->group(function () {
    // Where a bakery owner arrives from the "Manage billing" button in their bakery admin.
    Route::get('handoff/{token}', ConsumeBillingHandoffController::class)->name('handoff')->middleware('throttle:sensitive-write');

    Route::middleware('auth')->group(function () {
        Route::get('plans', ShowPlansController::class)->name('plans');
        Route::post('checkout/{plan}', CheckoutController::class)->name('checkout')->middleware('throttle:sensitive-write');
        Route::get('success', CheckoutSuccessController::class)->name('success');
        Route::get('portal', BillingPortalController::class)->name('portal');
        Route::post('swap/{plan}', SwapPlanController::class)->name('swap');
    });
});

// Stripe webhooks (excluded from CSRF)
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->middleware(RequireStripeWebhookSecret::class)
    ->name('cashier.webhook');

// Stripe Connect webhooks (for connected account events)
Route::post('stripe/connect-webhook', StripeConnectWebhookController::class)
    ->name('stripe.connect.webhook');
