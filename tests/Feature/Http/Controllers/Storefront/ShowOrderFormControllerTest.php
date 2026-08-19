<?php

use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order controller index passes settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('settings', fn (TenantSettings $settings): bool => true);
});

test('order controller passes page content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('content')
        ->assertViewHas('storefrontTheme');
});

test('biscotto order page uses the themed presentation without replacing the order form', function () {
    Setting::factory()->create(['key' => 'storefront_theme', 'value' => 'biscotto']);
    resolve(SettingsManager::class)->flushCache();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSee('biscotto-order-hero', false)
        ->assertSee('biscotto-order-stage', false)
        ->assertSee('data-test="order-form"', false)
        ->assertSee('data-test="order-form-submit"', false);
});
