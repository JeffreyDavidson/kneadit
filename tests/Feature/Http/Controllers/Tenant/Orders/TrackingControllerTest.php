<?php

use App\Mail\Orders\OrderTrackingLinkMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('tracking page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.track', absolute: false));

    $response->assertOk();
});

test('order tracking controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.track', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('storefrontTheme');
});

test('biscotto tracking page uses the themed follow-up presentation', function () {
    Setting::factory()->create(['key' => 'storefront_theme', 'value' => 'biscotto']);
    resolve(SettingsManager::class)->flushCache();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.track', [], false));

    $response->assertOk()->assertSeeHtml('biscotto-order-tracking')
        ->assertSee('Track Your Order');
});

const TRACKING_LINK_MESSAGE = "If we have orders for that email, we've sent you a link to view them.";

test('tracking with an email that has orders sends a link and shows no order data', function () {
    Mail::fake();
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    $order = Order::factory()
        ->for($customer)
        ->confirmed()
        ->create(['order_number' => 'KN260308A001']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'jane@example.com',
        ]);

    $response->assertRedirect(route('order.track', absolute: false));
    $response->assertSessionHas('status', TRACKING_LINK_MESSAGE);
    expect($response->getContent())->not->toContain('KN260308A001');
    Mail::assertQueued(OrderTrackingLinkMail::class, fn (OrderTrackingLinkMail $mail) => $mail->hasTo('jane@example.com'));
    withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false))
        ->assertRedirect(route('order.verify.show', ['order' => $order->order_number], false));
});

test('the page shows the same message after any lookup', function () {
    Mail::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->followingRedirects()
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'nobody@example.com',
        ]);

    $response->assertOk()->assertSee(TRACKING_LINK_MESSAGE);
});

test('tracking with an unknown email responds the same way and sends no mail', function () {
    Mail::fake();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'nobody@example.com',
        ]);

    $response->assertRedirect(route('order.track', absolute: false));
    $response->assertSessionHas('status', TRACKING_LINK_MESSAGE);
    Mail::assertNothingQueued();
});

test('tracking with an email that has no orders sends no mail', function () {
    Mail::fake();
    Customer::factory()->create(['email' => 'jane@example.com']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), [
            'email' => 'jane@example.com',
        ]);

    $response->assertSessionHas('status', TRACKING_LINK_MESSAGE);
    Mail::assertNothingQueued();
});

test('a repeat lookup within the cooldown sends no second mail', function () {
    Mail::fake();
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    Order::factory()->for($customer)->confirmed()->create();

    $first = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), ['email' => 'jane@example.com']);
    $second = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), ['email' => 'jane@example.com']);

    $second->assertSessionHas('status', TRACKING_LINK_MESSAGE);
    Mail::assertQueuedCount(1);
});

test('a lookup after the cooldown sends a new mail', function () {
    Mail::fake();
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    Order::factory()->for($customer)->confirmed()->create();

    withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), ['email' => 'jane@example.com']);
    test()->travel(6)->minutes();
    withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), ['email' => 'jane@example.com']);

    Mail::assertQueuedCount(2);
});

test('the queued mail carries a signed link that expires in 30 minutes', function () {
    Mail::fake();
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    Order::factory()->for($customer)->confirmed()->create();

    withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), ['email' => 'jane@example.com']);

    Mail::assertQueued(OrderTrackingLinkMail::class, function (OrderTrackingLinkMail $mail) use ($customer) {
        parse_str((string) parse_url($mail->trackingUrl, PHP_URL_QUERY), $query);

        return str_contains($mail->trackingUrl, "/track/access/{$customer->getKey()}")
            && (int) $query['expires'] === now()->addMinutes(30)->getTimestamp()
            && isset($query['signature']);
    });
});

test('tracking requires email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', absolute: false), []);

    $response->assertSessionHasErrors('email');
});
