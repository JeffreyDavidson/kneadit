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
