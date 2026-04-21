<?php

use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('cateringEventTypes falls back to enum default labels when not configured', function () {
    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(App\Enums\Customers\CateringEventType::defaultLabels());
});

test('cateringEventTypes resolves from a stored json array', function () {
    settings(['catering_event_types' => json_encode(['Kids Party', 'School Function'])]);

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(['Kids Party', 'School Function']);
});

test('cateringEventTypes falls back to defaults when the stored json is empty', function () {
    settings(['catering_event_types' => json_encode([])]);

    $settings = TenantSettings::resolve();

    expect($settings->catering->eventTypes)->toBe(App\Enums\Customers\CateringEventType::defaultLabels());
});

test('hero CTA properties fall back to defaults when unset in settings', function () {
    $settings = TenantSettings::resolve();

    expect($settings->branding->heroPrimaryCtaText)->toBe('Order Now')
        ->and($settings->branding->heroSecondaryCtaText)->toBe('Browse Menu')
        ->and($settings->branding->heroTagline)->toBeNull();
});

test('it is bound as a singleton in the container', function () {
    $a = app(TenantSettings::class);
    $b = app(TenantSettings::class);

    expect($a)->toBeInstanceOf(TenantSettings::class)
        ->and($a)->toBe($b);
});
