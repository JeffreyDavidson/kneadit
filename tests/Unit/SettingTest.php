<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    Setting::flushCache();
});

test('get returns value when exists', function () {
    Setting::query()->create(['key' => 'store_name', 'value' => 'Test Bakery']);

    expect(Setting::get('store_name'))->toBe('Test Bakery');
});

test('get returns default when not exists', function () {
    expect(Setting::get('nonexistent', 'fallback'))->toBe('fallback');
});

test('get returns null default', function () {
    expect(Setting::get('nonexistent'))->toBeNull();
});

test('set creates new setting', function () {
    Setting::set('new_key', 'new_value');

    assertDatabaseHas('settings', ['key' => 'new_key', 'value' => 'new_value']);
});

test('set updates existing setting', function () {
    Setting::set('my_key', 'old');
    Setting::set('my_key', 'new');

    expect(Setting::get('my_key'))->toBe('new');
    expect(Setting::query()->where('key', 'my_key')->count())->toBe(1);
});
