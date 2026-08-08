<?php

use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    resolve(SettingsManager::class)->flushCache();
});

test('get returns value when exists', function () {
    Setting::factory()->create(['key' => 'store_name', 'value' => 'Test Bakery']);

    expect(settings('store_name'))->toBe('Test Bakery');
});

test('get returns default when not exists', function () {
    expect(settings('nonexistent', 'fallback'))->toBe('fallback');
});

test('get returns null default', function () {
    expect(settings('nonexistent'))->toBeNull();
});

test('set creates new setting', function () {
    settings(['new_key' => 'new_value']);

    assertDatabaseHas('settings', ['key' => 'new_key', 'value' => 'new_value']);
});

test('set updates existing setting', function () {
    settings(['my_key' => 'old']);
    settings(['my_key' => 'new']);

    expect(settings('my_key'))->toBe('new')->and(Setting::query()->where('key', 'my_key')->count())->toBe(1);
});
