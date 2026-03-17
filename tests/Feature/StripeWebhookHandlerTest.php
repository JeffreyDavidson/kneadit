<?php

use App\Http\Controllers\StripeWebhookController;
use Laravel\Cashier\Http\Controllers\WebhookController;

beforeEach(function () {
    setUpCentralTest();
});

test('webhook controller extends cashier', function () {
    expect(is_subclass_of(StripeWebhookController::class, WebhookController::class))->toBeTrue();
});

test('webhook controller handles subscription updated', function () {
    expect(method_exists(StripeWebhookController::class, 'handleCustomerSubscriptionUpdated'))->toBeTrue();
});

test('webhook controller handles payment failed', function () {
    expect(method_exists(StripeWebhookController::class, 'handleInvoicePaymentFailed'))->toBeTrue();
});

test('webhook controller handles subscription deleted', function () {
    expect(method_exists(StripeWebhookController::class, 'handleCustomerSubscriptionDeleted'))->toBeTrue();
});

test('price id to plan mapping', function () {
    config(['saas.stripe_prices' => [
        'starter' => 'price_test_starter',
        'growth' => 'price_test_growth',
        'pro' => 'price_test_pro',
    ]]);

    $controller = new StripeWebhookController;
    $reflection = new ReflectionMethod($controller, 'priceIdToPlan');
    $reflection->setAccessible(true);

    expect($reflection->invoke($controller, 'price_test_starter'))->toBe('starter');
    expect($reflection->invoke($controller, 'price_test_growth'))->toBe('growth');
    expect($reflection->invoke($controller, 'price_test_pro'))->toBe('pro');
    expect($reflection->invoke($controller, 'price_unknown'))->toBeNull();
});

test('webhook route uses custom controller', function () {
    $source = file_get_contents(base_path('routes/billing.php'));

    expect($source)->toContain('StripeWebhookController');
    expect($source)->not->toContain('Cashier\Http\Controllers\WebhookController');
});

test('payment failed handler sends emails', function () {
    $source = file_get_contents(app_path('Http/Controllers/StripeWebhookController.php'));

    expect($source)->toContain('Payment failed');
    expect($source)->toContain('Mail::raw');
    expect($source)->toContain('platform_notify');
});
