<?php

use App\Models\Platform\PlatformSetting;

beforeEach(fn () => setUpCentralTest());

test('can create a platform setting', function () {
    PlatformSetting::factory()->create([
        'key' => 'site_name',
        'value' => 'KneadIt',
    ]);

    $found = PlatformSetting::query()->where('key', 'site_name')->first();

    expect($found)->not->toBeNull()
        ->and($found->value)->toBe('KneadIt');
});

test('value can be null', function () {
    $setting = PlatformSetting::factory()->create([
        'key' => 'optional_feature',
        'value' => null,
    ]);

    $setting->refresh();

    expect($setting->value)->toBeNull();
});

test('key and value are fillable', function () {
    $setting = PlatformSetting::create([
        'key' => 'max_tenants',
        'value' => '100',
    ]);

    expect($setting->key)->toBe('max_tenants')
        ->and($setting->value)->toBe('100');
});
