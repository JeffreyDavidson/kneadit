<?php

use App\DataTransferObjects\Settings\LoyaltySettings;
use App\DataTransferObjects\Settings\OrderSettings;
use App\DataTransferObjects\Settings\StoreInfo;
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

test('domain accessors return correct sub-DTOs', function () {
    $registry = resolve(TenantSettingsRegistry::class);

    expect($registry->store())->toBeInstanceOf(StoreInfo::class);
    expect($registry->orders())->toBeInstanceOf(OrderSettings::class);
    expect($registry->loyalty())->toBeInstanceOf(LoyaltySettings::class);
});

test('sub-DTOs are resolvable from the container', function () {
    expect(resolve(StoreInfo::class))->toBeInstanceOf(StoreInfo::class);
    expect(resolve(OrderSettings::class))->toBeInstanceOf(OrderSettings::class);
    expect(resolve(LoyaltySettings::class))->toBeInstanceOf(LoyaltySettings::class);
});

test('container-resolved sub-DTOs match TenantSettings properties', function () {
    $settings = resolve(TenantSettings::class);

    expect(resolve(StoreInfo::class)->name)->toBe($settings->store->name);
    expect(resolve(OrderSettings::class)->leadTimeHours)->toBe($settings->orders->leadTimeHours);
    expect(resolve(LoyaltySettings::class)->enabled)->toBe($settings->loyalty->enabled);
});

test('TenantSettings resolves through registry', function () {
    $registry = resolve(TenantSettingsRegistry::class);
    $settings = resolve(TenantSettings::class);

    expect($settings)->toBe($registry->all());
});
