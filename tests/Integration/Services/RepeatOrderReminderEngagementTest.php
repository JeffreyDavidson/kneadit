<?php

use App\Enums\Orders\PaymentStatus;
use App\Events\Customers\RepeatOrderReminderDue;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReminder;
use App\Models\Orders\Order;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Engagement\Engagements\RepeatOrderReminderEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isEnabled returns true when repeat reminders are enabled', function () {
    settings(['repeat_reminders_enabled' => '1']);

    $engagement = resolve(RepeatOrderReminderEngagement::class);

    expect($engagement->isEnabled(resolve(TenantSettings::class)))->toBeTrue();
});

test('isEnabled returns false when repeat reminders are disabled', function () {
    settings(['repeat_reminders_enabled' => '0']);

    $engagement = resolve(RepeatOrderReminderEngagement::class);

    expect($engagement->isEnabled(resolve(TenantSettings::class)))->toBeFalse();
});

test('findRecipients returns customers whose last paid order exceeds reminder days', function () {
    settings(['repeat_reminder_days' => '14']);

    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    $lastPaidOrderDate = now()->subDays(30);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => $lastPaidOrderDate,
        ]);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(90),
        ]);
    Order::factory()
        ->for($customer)
        ->create([
            'payment_status' => PaymentStatus::Unpaid,
            'delivery_date' => now()->subDay(),
        ]);

    $secondCustomer = Customer::factory()->create(['email' => 'returning@example.com']);
    Order::factory()
        ->for($secondCustomer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(60),
        ]);
    Order::factory()
        ->for($secondCustomer)
        ->create([
            'payment_status' => PaymentStatus::Paid,
            'delivery_date' => now()->subDays(20),
        ]);

    $engagement = resolve(RepeatOrderReminderEngagement::class);
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    $recipientsByEmail = $recipients->keyBy('email');

    expect($recipients)->toHaveCount(2)
        ->and($recipientsByEmail['loyal@example.com']->context['last_order_date']->toDateString())->toBe($lastPaidOrderDate->toDateString())
        ->and($recipientsByEmail['loyal@example.com']->model->orders)->toHaveCount(1)
        ->and($recipientsByEmail['returning@example.com']->model->orders)->toHaveCount(1);
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

    $engagement = resolve(RepeatOrderReminderEngagement::class);
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

    $engagement = resolve(RepeatOrderReminderEngagement::class);
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

    $engagement = resolve(RepeatOrderReminderEngagement::class);
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

// 2026-10-06 01:00 UTC is Monday 2026-10-05 21:00 in New York, so with a 14 day
// window the local cutoff is 2026-09-21 (the UTC one would be 2026-09-22).
function bindNewYorkRepeatReminders(): void
{
    app()->instance(TenantSettings::class, makeTenantSettings(
        orders: makeOrderSettings(['timezone' => 'America/New_York']),
        engagement: makeEngagementSettings(['repeatRemindersEnabled' => true, 'repeatReminderDays' => 14]),
    ));
    Date::setTestNow('2026-10-06 01:00');
}

test('findRecipients measures the reminder window from the bakery-local date', function (string $lastOrderDate, bool $isRecipient) {
    bindNewYorkRepeatReminders();
    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    Order::factory()->for($customer)->create([
        'payment_status' => PaymentStatus::Paid,
        'delivery_date' => $lastOrderDate,
    ]);

    $recipients = resolve(RepeatOrderReminderEngagement::class)->findRecipients(resolve(TenantSettings::class));

    expect($recipients->isNotEmpty())->toBe($isRecipient);
})->with([
    'last order on the local cutoff' => ['2026-09-21', true],
    'last order the day after the local cutoff' => ['2026-09-22', false],
]);

test('findRecipients counts days since the last order from the bakery-local date', function () {
    bindNewYorkRepeatReminders();
    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    Order::factory()->for($customer)->create([
        'payment_status' => PaymentStatus::Paid,
        'delivery_date' => '2026-09-21',
    ]);

    $recipients = resolve(RepeatOrderReminderEngagement::class)->findRecipients(resolve(TenantSettings::class));

    expect($recipients->first()->context['days_since_last_order'])->toBe(14);
});

test('findRecipients skips reminders scheduled after the bakery-local date', function (string $nextReminderDate, bool $isRecipient) {
    bindNewYorkRepeatReminders();
    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    Order::factory()->for($customer)->create([
        'payment_status' => PaymentStatus::Paid,
        'delivery_date' => '2026-09-01',
    ]);
    CustomerReminder::factory()->for($customer)->create(['next_reminder_date' => $nextReminderDate]);

    $recipients = resolve(RepeatOrderReminderEngagement::class)->findRecipients(resolve(TenantSettings::class));

    expect($recipients->isNotEmpty())->toBe($isRecipient);
})->with([
    'reminder due on the local day' => ['2026-10-05', true],
    'reminder due on the UTC day' => ['2026-10-06', false],
]);

test('dispatchForRecipient schedules the next reminder from the bakery-local date', function () {
    Event::fake([RepeatOrderReminderDue::class]);
    bindNewYorkRepeatReminders();
    $customer = Customer::factory()->create();
    $recipient = new EngagementRecipient(
        email: $customer->email,
        name: $customer->name,
        model: $customer,
        context: [
            'last_order_date' => Date::parse('2026-09-21'),
            'days_since_last_order' => 14,
            'reminder_days' => 14,
        ],
    );

    resolve(RepeatOrderReminderEngagement::class)->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    expect(CustomerReminder::query()->where('customer_id', $customer->id)->sole()->next_reminder_date->toDateString())->toBe('2026-10-19');
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

    $engagement = resolve(RepeatOrderReminderEngagement::class);
    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    Event::assertDispatched(RepeatOrderReminderDue::class);
    assertDatabaseHas('customer_reminders', [
        'customer_id' => $customer->id,
    ]);
});

test('a recipient from findRecipients is dispatched end to end and the reminder is sent', function () {
    Mail::fake();
    settings(['repeat_reminder_days' => '14']);
    $customer = Customer::factory()->create(['email' => 'loyal@example.com']);
    Order::factory()->for($customer)->create([
        'payment_status' => PaymentStatus::Paid,
        'delivery_date' => now()->subDays(30),
    ]);
    $engagement = resolve(RepeatOrderReminderEngagement::class);
    $recipient = $engagement->findRecipients(resolve(TenantSettings::class))->sole();

    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    assertDatabaseHas('customer_reminders', [
        'customer_id' => $customer->id,
    ]);
    Mail::assertQueued(RepeatOrderReminderMail::class, fn (RepeatOrderReminderMail $mail) => $mail->hasTo('loyal@example.com'));
});
