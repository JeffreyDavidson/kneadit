<?php

use App\Enums\Orders\PaymentMethod;
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

    $response->assertOk()->assertSeeHtml('biscotto-order-confirmation')
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

test('an unpaid card order offers a Pay now button that posts to the pay route', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);
    $order = Order::factory()->pending()->unpaid()->create([
        'payment_method' => PaymentMethod::Stripe,
        'stripe_checkout_session_id' => 'cs_confirm_open',
        'total' => 50.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertSee('Pay now')
        ->assertSeeHtml('action="'.route('order.pay', $order).'"');
});

dataset('orders without a Pay now button', [
    'already paid' => fn () => Order::factory()->pending()->paid()->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]),
    'cancelled' => fn () => Order::factory()->cancelled()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]),
    'paying by cash' => fn () => Order::factory()->pending()->unpaid()->create(['payment_method' => PaymentMethod::Cash, 'total' => 50.00]),
]);

test('an order that cannot be paid online has no Pay now button', function (Order $order) {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()->assertDontSee('Pay now');
})->with('orders without a Pay now button');

test('a card order has no Pay now button once the baker stops accepting card payments', function () {
    settings(['payment_methods' => json_encode(['cash'])]);
    $order = Order::factory()->pending()->unpaid()->create(['payment_method' => PaymentMethod::Stripe, 'total' => 50.00]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()->assertDontSee('Pay now');
});
