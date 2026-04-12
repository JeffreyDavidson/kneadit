<?php

use App\Services\Settings\PlatformSettingsManager;

beforeEach(fn () => setUpCentralTest());

test('can set and get a platform setting', function () {
    $manager = resolve(PlatformSettingsManager::class);
    $manager->flushCache();

    $manager->set('test_key', 'test_value');

    expect($manager->get('test_key'))->toBe('test_value');
});

test('returns default for missing key', function () {
    $manager = resolve(PlatformSettingsManager::class);
    $manager->flushCache();

    expect($manager->get('nonexistent', 'fallback'))->toBe('fallback');
});

test('set updates in-memory cache when already loaded', function () {
    $manager = resolve(PlatformSettingsManager::class);
    $manager->flushCache();

    // Load cache by calling get
    $manager->get('any_key');

    // Set a value — this should update the in-memory cache
    $manager->set('live_key', 'live_value');

    // Should read from cache, not require another loadAll
    expect($manager->get('live_key'))->toBe('live_value');
});
