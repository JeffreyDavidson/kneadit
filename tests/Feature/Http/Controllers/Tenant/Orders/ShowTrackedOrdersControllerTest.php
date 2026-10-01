<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    test()->customer = Customer::factory()->create(['email' => 'jane@example.com']);
});

function trackingAccessUrl(Customer $customer): string
{
    return URL::temporarySignedRoute('order.track.access', now()->addMinutes(30), ['customer' => $customer->getKey()]);
}

test('a signed link lists the customer orders and grants access to them', function () {
    $user = User::factory()->owner()->create();
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle($user)
        ->confirmed()
        ->create(['order_number' => 'KN260308A001']);

    $listing = withoutMiddleware(tenantMiddleware())
        ->get(trackingAccessUrl(test()->customer));
    $confirmation = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $listing->assertOk()->assertSee('KN260308A001');
    $confirmation->assertOk();
});

test('a signed link does not grant access to another customers orders', function () {
    $other = Order::factory()->create();
    Order::factory()->for(test()->customer)->confirmed()->create();

    withoutMiddleware(tenantMiddleware())
        ->get(trackingAccessUrl(test()->customer));
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $other->order_number], false));

    $response->assertRedirect(route('order.verify.show', ['order' => $other->order_number], false));
});

test('a link with a bad signature is rejected', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(trackingAccessUrl(test()->customer).'tampered');

    $response->assertForbidden();
});

test('an unsigned link is rejected', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.track.access', ['customer' => test()->customer->getKey()], false));

    $response->assertForbidden();
});

test('a signature for one customer cannot be reused for another', function () {
    $other = Customer::factory()->create();
    $url = str_replace("/track/access/{$other->getKey()}?", '/track/access/'.test()->customer->getKey().'?', trackingAccessUrl($other));

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertForbidden();
});

test('an expired link is rejected', function () {
    $url = trackingAccessUrl(test()->customer);

    test()->travel(31)->minutes();
    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertForbidden();
});

test('a logged-in customer with a verified email keeps direct access to their own order', function () {
    test()->customer->markEmailAsVerified();
    $order = Order::factory()->for(test()->customer)->confirmed()->create();

    actingAs(test()->customer, 'customer');
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk();
});

test('the listing links reorder and messages by order number', function () {
    $user = User::factory()->owner()->create();
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle($user)
        ->confirmed()
        ->create(['order_number' => 'KN260308BIND']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(trackingAccessUrl(test()->customer));

    $response->assertOk()
        ->assertSeeHtml("?reorder={$order->order_number}")
        ->assertSeeHtml("loadMessages('{$order->order_number}')")
        ->assertDontSeeHtml("?reorder={$order->id}\"")
        ->assertDontSeeHtml("loadMessages({$order->id})");
});

test('the listing shows an order in every status', function (string $status) {
    $user = User::factory()->owner()->create();
    Order::factory()
        ->for(test()->customer)
        ->recycle($user)
        ->create([
            'order_number' => 'KN'.strtoupper($status),
            'status' => $status,
            'subtotal' => 10.00,
            'total' => 10.00,
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(trackingAccessUrl(test()->customer));

    $response->assertOk()->assertSee('KN'.strtoupper($status));
})->with(['pending', 'confirmed', 'baking', 'ready', 'delivered']);

test('the listing displays items and totals', function () {
    $user = User::factory()->owner()->create();
    $category = Category::factory()->create(['name' => 'Breads', 'slug' => 'breads']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Baguette',
        'slug' => 'baguette',
        'price' => 5.00,
    ]);
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle($user)
        ->confirmed()
        ->create([
            'order_number' => 'KN260308ITEM',
            'subtotal' => 15.00,
            'total' => 15.00,
        ]);
    OrderItem::factory()
        ->for($order)
        ->for($product)
        ->create([
            'quantity' => 3,
            'unit_price' => 5.00,
        ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(trackingAccessUrl(test()->customer));

    $response->assertOk()
        ->assertSee('Baguette')
        ->assertSee('15.00');
});
