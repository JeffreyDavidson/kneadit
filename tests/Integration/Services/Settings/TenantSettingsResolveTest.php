<?php

use App\Enums\Customers\CateringEventType;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('cateringEventTypes falls back to enum default labels when not configured', function () {
    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(CateringEventType::defaultLabels());
});

test('cateringEventTypes resolves from a stored json array', function () {
    settings(['catering_event_types' => json_encode(['Kids Party', 'School Function'])]);

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(['Kids Party', 'School Function']);
});

test('cateringEventTypes falls back to defaults when the stored json is empty', function () {
    settings(['catering_event_types' => json_encode([])]);

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(CateringEventType::defaultLabels());
});

test('hero CTA properties fall back to defaults when unset in settings', function () {
    $settings = TenantSettings::resolve();

    expect($settings->branding->heroPrimaryCtaText)->toBe('Order Now')
        ->and($settings->branding->heroSecondaryCtaText)->toBe('Browse Menu')
        ->and($settings->branding->heroTagline)->toBeNull();
});

test('it is bound as a singleton in the container', function () {
    $a = resolve(TenantSettings::class);
    $b = resolve(TenantSettings::class);

    expect($a)->toBeInstanceOf(TenantSettings::class)
        ->and($a)->toBe($b);
});

test('lead time comes from the admin setting, then the older key, then the default', function (array $stored, int $expected) {
    settings($stored);

    $leadTimeHours = TenantSettings::resolve()->orders->leadTimeHours;

    expect($leadTimeHours)->toBe($expected);
})->with([
    'admin setting' => [['minimum_order_lead_hours' => '72'], 72],
    'admin setting wins over the older key' => [['minimum_order_lead_hours' => '72', 'order_lead_time_hours' => '24'], 72],
    'older key when the admin setting is missing' => [['order_lead_time_hours' => '36'], 36],
    'default when neither is set' => [[], 24],
]);

test('timezone comes from settings and falls back to UTC', function (array $stored, string $expected) {
    settings($stored);

    $timezone = TenantSettings::resolve()->orders->timezone;

    expect($timezone)->toBe($expected);
})->with([
    'a valid timezone' => [['timezone' => 'America/Chicago'], 'America/Chicago'],
    'not set' => [[], 'UTC'],
    'not a real timezone' => [['timezone' => 'Mars/Olympus'], 'UTC'],
]);
