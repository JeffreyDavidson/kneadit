<?php

use App\Models\Orders\Order;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order confirmation controller passes settings and content to view', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('storefrontTheme')
        ->assertViewHas('content')
        ->assertViewHas('journeySteps');
});

test('biscotto order confirmation uses the themed follow-up presentation', function () {
    Setting::factory()->create(['key' => 'storefront_theme', 'value' => 'biscotto']);
    resolve(SettingsManager::class)->flushCache();
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertSee('biscotto-order-confirmation', false)
        ->assertSee($order->order_number);
});

test('journey steps fall back to config defaults when no setting is stored', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk();
    expect($response->viewData('journeySteps'))->toEqual(config('kneadit.default_journey_steps'));
});

test('journey steps use the configured order_journey_steps setting when present', function () {
    $order = Order::factory()->create();

    $custom = [
        ['title' => 'Confirmed', 'description' => 'Order received.'],
        ['title' => 'Quality Check', 'description' => 'Every item inspected.'],
        ['title' => 'Handoff', 'description_delivery' => 'On the way.', 'description_pickup' => 'Ready for pickup.'],
    ];
    settings(['order_journey_steps' => json_encode($custom)]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk();
    expect($response->viewData('journeySteps'))->toEqual($custom);
});
