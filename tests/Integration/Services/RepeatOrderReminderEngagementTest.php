<?php

use App\Contracts\Engagement\EngagementRecipient;
use App\Enums\Orders\PaymentStatus;
use App\Events\Customers\RepeatOrderReminderDue;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReminder;
use App\Models\Orders\Order;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isEnabled returns true when repeat reminders are enabled', function () {
    settings(['repeat_reminders_enabled' => '1']);

    $engagement = new RepeatOrderReminderEngagement;

    expect($engagement->isEnabled(resolve(TenantSettings::class)))->toBeTrue();
});

test('isEnabled returns false when repeat reminders are disabled', function () {
    settings(['repeat_reminders_enabled' => '0']);

    $engagement = new RepeatOrderReminderEngagement;

    expect($engagement->isEnabled(resolve(TenantSettings::class)))->toBeFalse();
});

test('findRecipients returns customers whose last paid order exceeds reminder days', function () {
    settings(['repeat_reminder_days' => '14']);

    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(30),
        ]);

    $engagement = new RepeatOrderReminderEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()->email)->toBe('loyal@example.com');
});

test('findRecipients excludes customers with recent orders', function () {
    settings(['repeat_reminder_days' => '14']);

    $customer = Customer::factory()->create(['email' => 'recent@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(5),
        ]);

    $engagement = new RepeatOrderReminderEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes customers with no email', function () {
    settings(['repeat_reminder_days' => '14']);

    $customer = Customer::factory()->create(['email' => '']);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(30),
        ]);

    $engagement = new RepeatOrderReminderEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes customers with a future reminder scheduled', function () {
    settings(['repeat_reminder_days' => '14']);

    $customer = Customer::factory()->create(['email' => 'scheduled@example.com']);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(30),
        ]);
    CustomerReminder::query()->create([
        'customer_id' => $customer->id,
        'last_order_date' => now()->subDays(30),
        'reminder_sent_at' => now()->subDays(7),
        'next_reminder_date' => now()->addDays(7),
    ]);

    $engagement = new RepeatOrderReminderEngagement;
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('dispatchForRecipient creates a reminder record and dispatches event', function () {
    Event::fake([RepeatOrderReminderDue::class]);
    settings(['repeat_reminder_days' => '14']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create([
        'payment_status' => PaymentStatus::Paid,
        'delivery_date' => now()->subDays(20),
    ]);

    $recipient = new EngagementRecipient(
        email: $customer->email,
        name: $customer->name,
        model: $customer,
        context: [
            'last_order_date' => $order->delivery_date,
            'days_since_last_order' => 20,
            'reminder_days' => 14,
        ],
    );

    $engagement = new RepeatOrderReminderEngagement;
    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    Event::assertDispatched(RepeatOrderReminderDue::class);
    $this->assertDatabaseHas('customer_reminders', [
        'customer_id' => $customer->id,
    ]);
});
