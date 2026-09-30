<?php

use App\Enums\Orders\OrderStatus;
use App\Mail\Customers\ReviewRequestMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

beforeEach(fn () => setUpCentralTest());

/**
 * Route the command through a single already-active tenant, since the test
 * environment has no central tenant rows to iterate.
 *
 * @param  array<string, bool|int>  $engagement
 */
function runReviewRequestsForTenant(array $engagement): void
{
    $settings = makeTenantSettings(engagement: makeEngagementSettings($engagement));

    $tenancyManager = new class($settings) extends TenancyManager
    {
        public function __construct(private readonly TenantSettings $settings)
        {
            parent::__construct(
                app(SettingsManager::class),
                app(TenantSettingsRegistry::class),
            );
        }

        public function forEachTenant(callable $callback, ?callable $onError = null): int
        {
            $callback(new Tenant(['id' => 'test-tenant']), $this->settings);

            return 0;
        }
    };

    app()->instance(TenancyManager::class, $tenancyManager);
}

test('review requests command runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('reviews:send-requests')
        ->assertSuccessful();

    Mail::assertNothingSent();
});

test('it sends one review request for an eligible delivered order and marks it', function () {
    Date::setTestNow('2026-09-30 12:00');
    Mail::fake();
    runReviewRequestsForTenant(['reviewRequestsEnabled' => true, 'reviewRequestDelayHours' => 24]);

    $order = Order::factory()
        ->for(Customer::factory()->state(['email' => 'buyer@example.com']))
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours(48),
        ]);

    artisan('reviews:send-requests')->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertQueued(ReviewRequestMail::class, fn (ReviewRequestMail $mail): bool => $mail->hasTo('buyer@example.com'));
    expect($order->fresh()->review_request_sent_at)->not->toBeNull();
});

test('it sends nothing for ineligible orders', function (bool $enabled, OrderStatus $status, int $hoursSinceUpdate, bool $alreadySent) {
    Date::setTestNow('2026-09-30 12:00');
    Mail::fake();
    runReviewRequestsForTenant(['reviewRequestsEnabled' => $enabled, 'reviewRequestDelayHours' => 24]);

    $order = Order::factory()
        ->for(Customer::factory()->state(['email' => 'buyer@example.com']))
        ->create([
            'status' => $status,
            'review_request_sent_at' => $alreadySent ? now()->subDay() : null,
            'updated_at' => now()->subHours($hoursSinceUpdate),
        ]);
    $sentAt = $order->review_request_sent_at;

    artisan('reviews:send-requests')->assertSuccessful();

    Mail::assertNothingOutgoing();
    expect($order->fresh()->review_request_sent_at?->toIso8601String())->toBe($sentAt?->toIso8601String());
})->with([
    'reviews disabled' => [false, OrderStatus::Delivered, 48, false],
    'inside the delay window' => [true, OrderStatus::Delivered, 23, false],
    'not delivered' => [true, OrderStatus::Confirmed, 48, false],
    'already requested' => [true, OrderStatus::Delivered, 48, true],
]);

test('it respects the exact delay boundary', function (int $hoursSinceUpdate, int $expectedSent) {
    Date::setTestNow('2026-09-30 12:00');
    Mail::fake();
    runReviewRequestsForTenant(['reviewRequestsEnabled' => true, 'reviewRequestDelayHours' => 24]);

    Order::factory()
        ->for(Customer::factory()->state(['email' => 'buyer@example.com']))
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours($hoursSinceUpdate),
        ]);

    artisan('reviews:send-requests')->assertSuccessful();

    Mail::assertQueuedCount($expectedSent);
})->with([
    'exactly at the cutoff' => [24, 1],
    'just inside the cutoff' => [23, 0],
]);
