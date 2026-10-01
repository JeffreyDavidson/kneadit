<?php

use App\Models\Operations\BusinessSchedule;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order controller index passes settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('settings', fn (TenantSettings $s) => is_int($s->orders->leadTimeHours) && is_bool($s->orders->deliveryEnabled));
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

    $response->assertOk()->assertSeeHtml('biscotto-order-hero')->assertSeeHtml('biscotto-order-stage')->assertSeeHtml('data-test="order-form"')->assertSeeHtml('data-test="order-form-submit"');
});

test('order page lists the payment methods the baker accepts', function () {
    settings(['payment_methods' => json_encode(['cash', 'paypal'])]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSee('Cash, Paypal');
});

test('order page shows and enforces the earliest delivery date after the order cutoff', function () {
    settings(['minimum_order_lead_hours' => '48', 'timezone' => 'America/New_York']);
    BusinessSchedule::factory()->open()->create(['day_of_week' => 1, 'order_cutoff_time' => '14:00']);
    Date::setTestNow('2026-10-05 19:30');

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSee('ready Thursday, October 8 or later')
        ->assertSeeHtml("minDate: '2026-10-08'");
});

test('the coupon apply button closes its opening tag before its label', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    expect($response->getContent())->toMatch('/data-test="order-form-coupon-apply"[^>]*>\s*<span x-text="isApplyingCoupon/');
});
