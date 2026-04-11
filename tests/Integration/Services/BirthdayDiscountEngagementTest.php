<?php

use App\Contracts\Engagement\EngagementRecipient;
use App\Events\Customers\BirthdayDiscountGenerated;
use App\Models\Customers\Customer;
use App\Services\Engagement\Engagements\BirthdayDiscountEngagement;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isEnabled returns true when birthday program is enabled', function () {
    settings(['birthday_program_enabled' => '1']);

    $engagement = app(BirthdayDiscountEngagement::class);

    expect($engagement->isEnabled(app(TenantSettings::class)))->toBeTrue();
});

test('isEnabled returns false when birthday program is disabled', function () {
    settings(['birthday_program_enabled' => '0']);

    $engagement = app(BirthdayDiscountEngagement::class);

    expect($engagement->isEnabled(app(TenantSettings::class)))->toBeFalse();
});

test('findRecipients returns customers with today birthday and email', function () {
    Customer::factory()->create([
        'birthday' => now(),
        'email' => 'birthday@example.com',
    ]);

    $engagement = app(BirthdayDiscountEngagement::class);
    $recipients = $engagement->findRecipients(app(TenantSettings::class));

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()->email)->toBe('birthday@example.com');
});

test('findRecipients excludes customers without a birthday', function () {
    Customer::factory()->create([
        'birthday' => null,
        'email' => 'no-birthday@example.com',
    ]);

    $engagement = app(BirthdayDiscountEngagement::class);
    $recipients = $engagement->findRecipients(app(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes customers without an email', function () {
    Customer::factory()->create([
        'birthday' => now(),
        'email' => '',
    ]);

    $engagement = app(BirthdayDiscountEngagement::class);
    $recipients = $engagement->findRecipients(app(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('findRecipients excludes customers with a birthday on a different day', function () {
    Customer::factory()->create([
        'birthday' => now()->subDays(5),
        'email' => 'wrong-day@example.com',
    ]);

    $engagement = app(BirthdayDiscountEngagement::class);
    $recipients = $engagement->findRecipients(app(TenantSettings::class));

    expect($recipients)->toBeEmpty();
});

test('dispatchForRecipient creates coupon and dispatches BirthdayDiscountGenerated event', function () {
    Event::fake([BirthdayDiscountGenerated::class]);
    settings([
        'birthday_program_enabled' => '1',
        'birthday_discount_percentage' => '20',
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

    $engagement = app(BirthdayDiscountEngagement::class);
    $engagement->dispatchForRecipient($recipient, app(TenantSettings::class));

    Event::assertDispatched(BirthdayDiscountGenerated::class);
    $this->assertDatabaseHas('coupons', [
        'value' => 20,
    ]);
});
