<?php

use App\Events\Customers\CustomerBirthday;
use App\Models\Customers\Customer;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Engagement\Engagements\BirthdayEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isEnabled returns true when birthday program is enabled', function () {
    settings(['birthday_program_enabled' => '1']);

    $engagement = resolve(BirthdayEngagement::class);

    expect($engagement->isEnabled(resolve(TenantSettings::class)))->toBeTrue();
});

test('isEnabled returns false when birthday program is disabled', function () {
    settings(['birthday_program_enabled' => '0']);

    $engagement = resolve(BirthdayEngagement::class);

    expect($engagement->isEnabled(resolve(TenantSettings::class)))->toBeFalse();
});

test('findRecipients returns customers with today birthday and email', function () {
    Customer::factory()->create([
        'birthday' => now(),
        'email' => 'birthday@example.com',
    ]);

    $engagement = resolve(BirthdayEngagement::class);
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()->email)->toBe('birthday@example.com');
});

test('findRecipients excludes customers without a birthday', function () {
    Customer::factory()->create([
        'birthday' => null,
        'email' => 'no-birthday@example.com',
    ]);

    $engagement = resolve(BirthdayEngagement::class);
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes customers without an email', function () {
    Customer::factory()->create([
        'birthday' => now(),
        'email' => '',
    ]);

    $engagement = resolve(BirthdayEngagement::class);
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes customers with a birthday on a different day', function () {
    Customer::factory()->create([
        'birthday' => now()->subDays(5),
        'email' => 'wrong-day@example.com',
    ]);

    $engagement = resolve(BirthdayEngagement::class);
    $recipients = $engagement->findRecipients(resolve(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients selects birthdays on the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    Customer::factory()->create(['birthday' => '1990-10-05', 'email' => 'local-day@example.com']);
    Customer::factory()->create(['birthday' => '1990-10-06', 'email' => 'utc-day@example.com']);

    $recipients = resolve(BirthdayEngagement::class)->findRecipients(resolve(TenantSettings::class));

    expect($recipients->pluck('email')->all())->toBe(['local-day@example.com']);
});

test('dispatchForRecipient creates coupon and dispatches event when coupon enabled', function () {
    Event::fake([CustomerBirthday::class]);
    settings([
        'birthday_program_enabled' => '1',
        'birthday_coupon_enabled' => '1',
        'birthday_discount_percentage' => '15',
        'birthday_coupon_valid_days' => '14',
    ]);

    $customer = Customer::factory()->create([
        'birthday' => now(),
        'email' => 'birthday@example.com',
    ]);

    $recipient = new EngagementRecipient(
        email: $customer->email,
        name: $customer->name,
        model: $customer,
    );

    $engagement = resolve(BirthdayEngagement::class);
    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    Event::assertDispatched(fn (CustomerBirthday $event) => $event->coupon !== null);
    assertDatabaseHas('coupons', [
        'percentage' => 15,
    ]);
});

test('dispatchForRecipient dispatches event without coupon when coupon disabled', function () {
    Event::fake([CustomerBirthday::class]);
    settings([
        'birthday_program_enabled' => '1',
        'birthday_coupon_enabled' => '0',
    ]);

    $customer = Customer::factory()->create([
        'birthday' => now(),
        'email' => 'birthday@example.com',
    ]);

    $recipient = new EngagementRecipient(
        email: $customer->email,
        name: $customer->name,
        model: $customer,
    );

    $engagement = resolve(BirthdayEngagement::class);
    $engagement->dispatchForRecipient($recipient, resolve(TenantSettings::class));

    Event::assertDispatched(fn (CustomerBirthday $event) => $event->coupon === null);
    assertDatabaseCount('coupons', 0);
});
