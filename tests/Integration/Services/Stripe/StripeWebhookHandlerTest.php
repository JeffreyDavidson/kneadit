<?php

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Stripe\StripeWebhookEventHandler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
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

test('price id to plan mapping uses SubscriptionTier::fromPriceId', function () {
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_test_starter',
        'growth' => 'price_test_growth',
        'pro' => 'price_test_pro',
    ]]);

    expect(SubscriptionTier::fromPriceId('price_test_starter'))->toBe(SubscriptionTier::Starter)
        ->and(SubscriptionTier::fromPriceId('price_test_growth'))->toBe(SubscriptionTier::Growth)
        ->and(SubscriptionTier::fromPriceId('price_test_pro'))->toBe(SubscriptionTier::Pro)
        ->and(SubscriptionTier::fromPriceId('price_unknown'))->toBeNull();
});

test('webhook route uses custom controller', function () {
    $source = file_get_contents(base_path('routes/billing.php'));

    expect($source)->toContain('StripeWebhookController')->not->toContain('Cashier\Http\Controllers\WebhookController');
});

test('payment failed handler dispatches PaymentFailed event', function () {
    $source = file_get_contents(app_path('Services/Stripe/StripeWebhookEventHandler.php'));

    expect($source)->toContain('Payment failed')->toContain('new PaymentFailed(');
});

test('subscription updated syncs the plan to the owner\'s bakery after an email change', function () {
    Config::set('kneadit.stripe_prices', ['starter' => 'price_starter', 'growth' => 'price_growth']);
    $owner = User::factory()->create(['stripe_id' => 'cus_renamed', 'email' => 'new-address@example.com']);
    createTenant(['id' => 'renamed-bakery', 'email' => 'old-address@example.com', 'user_id' => $owner->id, 'plan' => 'starter']);

    resolve(StripeWebhookEventHandler::class)
        ->handleSubscriptionUpdated(['customer' => 'cus_renamed', 'items' => ['data' => [['price' => ['id' => 'price_growth']]]]]);

    expect(Tenant::query()->findOrFail('renamed-bakery')->plan)->toBe(SubscriptionTier::Growth);
});

test('subscription updated logs a warning and changes nothing when the customer owns no bakery', function () {
    Config::set('kneadit.stripe_prices', ['starter' => 'price_starter', 'growth' => 'price_growth']);
    Log::spy();
    User::factory()->create(['stripe_id' => 'cus_no_bakery', 'email' => 'baker@example.com']);
    createTenant(['id' => 'unowned-bakery', 'email' => 'baker@example.com', 'user_id' => null, 'plan' => 'starter']);

    resolve(StripeWebhookEventHandler::class)
        ->handleSubscriptionUpdated(['customer' => 'cus_no_bakery', 'items' => ['data' => [['price' => ['id' => 'price_growth']]]]]);

    expect(Tenant::query()->findOrFail('unowned-bakery')->plan)->toBe(SubscriptionTier::Starter);
    Log::shouldHaveReceived('warning')->with('Tenant not found for subscription update', ['stripe_customer' => 'cus_no_bakery']);
});
