<?php

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Settings\CateringSettings;
use App\DataTransferObjects\Settings\EngagementSettings;
use App\DataTransferObjects\Settings\HomepageSettings;
use App\DataTransferObjects\Settings\LoyaltySettings;
use App\DataTransferObjects\Settings\OnboardingSettings;
use App\DataTransferObjects\Settings\OrderSettings;
use App\DataTransferObjects\Settings\PaymentSettings;
use App\DataTransferObjects\Settings\PolicySettings;
use App\DataTransferObjects\Settings\StoreInfo;
use App\DataTransferObjects\Settings\WebhookSettings;
use App\Services\Settings\TenantSettings;
use App\Services\Settings\TenantSettingsRegistry;

beforeEach(fn () => setUpTenantTest());

test('all() returns TenantSettings instance', function () {
    $registry = resolve(TenantSettingsRegistry::class);

    expect($registry->all())->toBeInstanceOf(TenantSettings::class);
});

test('all() caches the result within the same request', function () {
    $registry = resolve(TenantSettingsRegistry::class);

    $first = $registry->all();
    $second = $registry->all();

    expect($first)->toBe($second);
});

test('flush() resets the cached instance', function () {
    $registry = resolve(TenantSettingsRegistry::class);

    $first = $registry->all();
    $registry->flush();
    $second = $registry->all();

    expect($first)->not->toBe($second);
});

test('each accessor returns the correct sub-DTO type', function (string $accessor, string $expectedClass) {
    $registry = resolve(TenantSettingsRegistry::class);

    expect($registry->{$accessor}())->toBeInstanceOf($expectedClass);
})->with([
    'store' => ['store', StoreInfo::class],
    'branding' => ['branding', BrandingSettings::class],
    'orders' => ['orders', OrderSettings::class],
    'payment' => ['payment', PaymentSettings::class],
    'loyalty' => ['loyalty', LoyaltySettings::class],
    'catering' => ['catering', CateringSettings::class],
    'engagement' => ['engagement', EngagementSettings::class],
    'policies' => ['policies', PolicySettings::class],
    'webhooks' => ['webhooks', WebhookSettings::class],
    'homepage' => ['homepage', HomepageSettings::class],
    'onboarding' => ['onboarding', OnboardingSettings::class],
]);

test('sub-DTOs are resolvable from the container', function () {
    expect(resolve(StoreInfo::class))->toBeInstanceOf(StoreInfo::class)
        ->and(resolve(OrderSettings::class))->toBeInstanceOf(OrderSettings::class)
        ->and(resolve(LoyaltySettings::class))->toBeInstanceOf(LoyaltySettings::class);
});

test('container-resolved sub-DTOs match TenantSettings properties', function () {
    $settings = resolve(TenantSettings::class);

    expect(resolve(StoreInfo::class)->name)->toBe($settings->store->name)
        ->and(resolve(OrderSettings::class)->leadTimeHours)->toBe($settings->orders->leadTimeHours)
        ->and(resolve(LoyaltySettings::class)->enabled)->toBe($settings->loyalty->enabled);
});

test('TenantSettings resolves through registry', function () {
    $registry = resolve(TenantSettingsRegistry::class);
    $settings = resolve(TenantSettings::class);

    expect($settings)->toBe($registry->all());
});
