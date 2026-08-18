<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Customers\ReviewRequested;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Engagement\Engagements\ReviewRequestEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isEnabled returns true when review requests are enabled', function () {
    settings(['review_requests_enabled' => '1']);

    $engagement = new ReviewRequestEngagement;
    $settings = resolve(TenantSettings::class);

    expect($engagement->isEnabled($settings))->toBeTrue();
});

test('isEnabled returns false when review requests are disabled', function () {
    settings(['review_requests_enabled' => '0']);

    $engagement = new ReviewRequestEngagement;
    $settings = resolve(TenantSettings::class);

    expect($engagement->isEnabled($settings))->toBeFalse();
});

test('findRecipients returns delivered orders past delay threshold', function () {
    settings(['review_request_delay_hours' => '24']);

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours(48),
        ]);

    $engagement = new ReviewRequestEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()->email)->toBe('buyer@example.com');
});

test('findRecipients excludes orders already sent a review request', function () {
    settings(['review_request_delay_hours' => '24']);

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => now()->subDay(),
            'updated_at' => now()->subHours(48),
        ]);

    $engagement = new ReviewRequestEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes orders within delay threshold', function () {
    settings(['review_request_delay_hours' => '24']);

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours(12),
        ]);

    $engagement = new ReviewRequestEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes non-delivered orders', function () {
    settings(['review_request_delay_hours' => '24']);

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'status' => OrderStatus::Confirmed,
            'review_request_sent_at' => null,
            'updated_at' => now()->subHours(48),
        ]);

    $engagement = new ReviewRequestEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('dispatchForRecipient dispatches ReviewRequested event and marks order', function () {
    Event::fake([ReviewRequested::class]);

    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
        ]);

    $recipient = new App\Contracts\Engagement\EngagementRecipient(
        email: $customer->email,
        name: $customer->name,
        model: $order,
    );

    $engagement = new ReviewRequestEngagement;
    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    Event::assertDispatched(ReviewRequested::class);
    expect($order->fresh()->review_request_sent_at)->not->toBeNull();
});
