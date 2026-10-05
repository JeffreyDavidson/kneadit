<?php

use App\Actions\Stripe\HandleConnectCheckoutCompleted;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('it does nothing when session has no id', function () {
    $session = ['id' => null, 'metadata' => []];

    resolve(HandleConnectCheckoutCompleted::class)($session);

    expect(true)->toBeTrue();
});

test('it does nothing when tenant id is missing from metadata', function () {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'missing tenant_id'));

    $session = [
        'id' => 'cs_test_456',
        'payment_status' => 'paid',
        'metadata' => ['order_id' => 1],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});

test('it does nothing when tenant is not found', function () {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Tenant not found'));

    $session = [
        'id' => 'cs_test_789',
        'payment_status' => 'paid',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'nonexistent-tenant',
        ],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});

test('it processes checkout session within tenant context', function () {
    $tenant = createTenant(['id' => 'checkout-tenant', 'email' => 'checkout@test.com']);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('info')->andReturnNull();

    $session = [
        'id' => 'cs_test_success',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_success',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'checkout-tenant',
        ],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});

test('it propagates exceptions during tenant context processing for webhook retry', function () {
    $tenant = createTenant(['id' => 'error-tenant', 'email' => 'error@test.com']);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new Exception('Processing failed'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Error processing checkout session'));

    $session = [
        'id' => 'cs_test_error',
        'payment_status' => 'paid',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'error-tenant',
        ],
    ];

    expect(fn () => resolve(HandleConnectCheckoutCompleted::class)($session))
        ->toThrow(Exception::class, 'Processing failed');
});

function connectCheckoutSession(int $amountTotal): array
{
    return [
        'id' => 'cs_test_amount',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_amount',
        'amount_total' => $amountTotal,
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'amount-tenant',
        ],
    ];
}

function runWebhookWithinTenant(): void
{
    createTenant(['id' => 'amount-tenant', 'email' => 'amount@test.com']);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);
}

test('it marks the order paid when the amount paid equals the order total', function () {
    runWebhookWithinTenant();
    $order = Order::factory()->unpaid()->create(['id' => 1, 'stripe_checkout_session_id' => 'cs_test_amount', 'total' => 50.00]);

    resolve(HandleConnectCheckoutCompleted::class)(connectCheckoutSession(5000));

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->stripe_payment_intent_id)->toBe('pi_test_amount');
});

test('it leaves the order unpaid and notifies the baker when the amount paid differs', function (int $amountPaid) {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message): bool => str_contains($message, 'amount does not match'));
    runWebhookWithinTenant();
    $owner = User::factory()->owner()->create();
    $order = Order::factory()->unpaid()->create(['id' => 1, 'stripe_checkout_session_id' => 'cs_test_amount', 'total' => 50.00]);

    resolve(HandleConnectCheckoutCompleted::class)(connectCheckoutSession($amountPaid));

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->stripe_payment_intent_id)->toBe('pi_test_amount')
        ->and($owner->notifications()->count())->toBe(1);
})->with([
    'lower than the order total' => 4000,
    'higher than the order total' => 6000,
]);

/**
 * @return array<string, mixed>
 */
function cateringConnectSession(CateringInquiry $inquiry, string $sessionId = 'cs_test_older', string $paymentIntentId = 'pi_test_older'): array
{
    return [
        'id' => $sessionId,
        'payment_status' => 'paid',
        'payment_intent' => $paymentIntentId,
        'amount_total' => 25000,
        'metadata' => [
            'catering_inquiry_id' => (string) $inquiry->id,
            'tenant_id' => 'amount-tenant',
        ],
    ];
}

test('it records a catering deposit paid on a session that is not the latest one stored on the inquiry', function () {
    runWebhookWithinTenant();
    $inquiry = CateringInquiry::factory()->quoted()->create(['stripe_checkout_session_id' => 'cs_test_newer']);

    resolve(HandleConnectCheckoutCompleted::class)(cateringConnectSession($inquiry));

    expect($inquiry->refresh()->deposit_paid_at)->not->toBeNull()
        ->and($inquiry->deposit_amount?->dollars())->toBe(250.00)
        ->and($inquiry->deposit_reference)->toBe('pi_test_older')
        ->and($inquiry->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('it does not record a catering deposit for an inquiry that no longer accepts deposits and notifies the baker', function (CateringInquiryStatus $status) {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $context['session_id'] === 'cs_test_older'
            && $context['payment_intent'] === 'pi_test_older');
    runWebhookWithinTenant();
    $owner = User::factory()->owner()->create();
    $inquiry = CateringInquiry::factory()->create(['status' => $status, 'stripe_checkout_session_id' => 'cs_test_older']);

    resolve(HandleConnectCheckoutCompleted::class)(cateringConnectSession($inquiry));

    expect($inquiry->refresh()->deposit_paid_at)->toBeNull()
        ->and($inquiry->status)->toBe($status)
        ->and($owner->notifications()->count())->toBe(1);
})->with([
    'cancelled' => CateringInquiryStatus::Cancelled,
    'completed' => CateringInquiryStatus::Completed,
]);

test('it leaves a recorded catering deposit unchanged when another session is paid and notifies the baker', function () {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $context['payment_intent'] === 'pi_test_second');
    runWebhookWithinTenant();
    $owner = User::factory()->owner()->create();
    $inquiry = CateringInquiry::factory()->confirmed()->create([
        'deposit_amount' => 100,
        'deposit_paid_at' => now()->subDay(),
        'deposit_reference' => 'pi_test_first',
        'stripe_payment_intent_id' => 'pi_test_first',
    ]);

    resolve(HandleConnectCheckoutCompleted::class)(cateringConnectSession($inquiry, 'cs_test_second', 'pi_test_second'));

    expect($inquiry->refresh()->deposit_amount?->dollars())->toBe(100.00)
        ->and($inquiry->deposit_reference)->toBe('pi_test_first')
        ->and($inquiry->stripe_payment_intent_id)->toBe('pi_test_first')
        ->and($owner->notifications()->count())->toBe(1);
});

test('it does not alert the baker when the same catering payment is delivered again', function () {
    runWebhookWithinTenant();
    $owner = User::factory()->owner()->create();
    $inquiry = CateringInquiry::factory()->confirmed()->create([
        'deposit_amount' => 250,
        'deposit_paid_at' => now()->subMinute(),
        'deposit_reference' => 'pi_test_older',
        'stripe_payment_intent_id' => 'pi_test_older',
    ]);

    resolve(HandleConnectCheckoutCompleted::class)(cateringConnectSession($inquiry));

    expect($inquiry->refresh()->deposit_reference)->toBe('pi_test_older')
        ->and($owner->notifications()->count())->toBe(0);
});
