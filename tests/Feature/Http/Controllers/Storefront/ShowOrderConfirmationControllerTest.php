<?php

use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order confirmation controller passes settings and content to view', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('journeySteps');
});

test('journey steps fall back to config defaults when no setting is stored', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
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
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk();
    expect($response->viewData('journeySteps'))->toEqual($custom);
});
