<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Customers\ReviewRequested;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Engagement\Engagements\ReviewRequestEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
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

    $recipient = new EngagementRecipient(
        email: $customer->email,
        name: $customer->name,
        model: $order,
    );

    $engagement = new ReviewRequestEngagement;
    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    Event::assertDispatched(ReviewRequested::class);
    expect($order->fresh()->review_request_sent_at)->not->toBeNull();
});

describe('when review requests are first switched on', function () {
    beforeEach(function () {
        Date::setTestNow('2026-10-05 12:00');
        settings(['review_request_delay_hours' => '24']);
        test()->customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    });

    function deliveredOrderFor(Customer $customer, string $deliveredAt): Order
    {
        return Order::factory()->for($customer)->create([
            'status' => OrderStatus::Delivered,
            'review_request_sent_at' => null,
            'created_at' => $deliveredAt,
            'updated_at' => $deliveredAt,
        ]);
    }

    test('an order delivered more than a week ago is never asked for a review', function () {
        deliveredOrderFor(test()->customer, '2026-09-05 12:00');

        $recipients = resolve(ReviewRequestEngagement::class)->findRecipients(resolve(TenantSettings::class));

        expect($recipients)->toBeEmpty();
    });

    test('a customer with several recent orders is asked once, about the most recent', function () {
        deliveredOrderFor(test()->customer, '2026-09-05 12:00');
        $newest = deliveredOrderFor(test()->customer, '2026-10-03 12:00');
        $older = deliveredOrderFor(test()->customer, '2026-10-02 12:00');

        $recipients = resolve(ReviewRequestEngagement::class)->findRecipients(resolve(TenantSettings::class));

        expect($recipients)->toHaveCount(1)
            ->and($recipients->first()->model->is($newest))->toBeTrue()
            ->and($older->review_request_sent_at)->toBeNull();
    });

    test('the other recent orders are marked handled so they are not asked about later', function () {
        deliveredOrderFor(test()->customer, '2026-09-05 12:00');
        $newest = deliveredOrderFor(test()->customer, '2026-10-03 12:00');
        $older = deliveredOrderFor(test()->customer, '2026-10-02 12:00');
        $engagement = resolve(ReviewRequestEngagement::class);
        $settings = resolve(TenantSettings::class);

        $engagement->dispatchForRecipient($engagement->findRecipients($settings)->first(), $settings);

        expect($older->fresh()->review_request_sent_at)->not->toBeNull()
            ->and($newest->fresh()->review_request_sent_at)->not->toBeNull()
            ->and($engagement->findRecipients($settings))->toBeEmpty();
    });

    test('two customers are each asked once', function () {
        $other = Customer::factory()->create(['email' => 'other@example.com']);
        deliveredOrderFor(test()->customer, '2026-10-03 12:00');
        deliveredOrderFor($other, '2026-10-02 12:00');

        $recipients = resolve(ReviewRequestEngagement::class)->findRecipients(resolve(TenantSettings::class));

        expect($recipients->pluck('email')->sort()->values()->all())->toBe(['buyer@example.com', 'other@example.com']);
    });

    test('the window follows the delay, so a long delay still sends', function () {
        settings(['review_request_delay_hours' => '200']);
        $afterDelay = deliveredOrderFor(test()->customer, '2026-09-26 12:00');
        $tooOld = deliveredOrderFor(Customer::factory()->create(['email' => 'old@example.com']), '2026-09-15 12:00');

        $recipients = resolve(ReviewRequestEngagement::class)->findRecipients(resolve(TenantSettings::class));

        expect($recipients)->toHaveCount(1)
            ->and($recipients->first()->model->is($afterDelay))->toBeTrue()
            ->and($tooOld->review_request_sent_at)->toBeNull();
    });
});
