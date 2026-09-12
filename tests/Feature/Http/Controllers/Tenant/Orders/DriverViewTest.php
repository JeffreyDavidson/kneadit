<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    test()->driverMiddleware = [
        InitializeTenancyByDomainOrSubdomain::class,
        PreventAccessFromCentralDomains::class,
    ];
});

function createDeliveryOrder(array $attrs = []): Order
{
    $user = User::factory()->owner()->create(['email' => 'baker@test.com']);
    $customer = Customer::factory()->create();

    return Order::factory()
        ->for($customer)
        ->recycle($user)
        ->confirmed()
        ->create(array_merge([
            'delivery_address' => '123 Main St',
            'delivery_date' => today(),
        ], $attrs));
}

test('driver page loads', function () {
    settings(['store_name' => 'Test Bakery']);

    $response = withoutMiddleware(test()->driverMiddleware)->get(route('driver.index', [], false));

    $response->assertOk();
});

test('driver page shows todays delivery orders', function () {
    settings(['store_name' => 'Test Bakery']);
    $order = createDeliveryOrder(['order_number' => 'ORD-001']);

    $response = withoutMiddleware(test()->driverMiddleware)->get(route('driver.index', [], false));

    $response->assertOk();
    $response->assertSee('ORD-001');
});

test('driver page hides pickup orders', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-PICKUP', 'delivery_address' => '']);

    $response = withoutMiddleware(test()->driverMiddleware)->get(route('driver.index', [], false));

    $response->assertOk();
    $response->assertDontSee('ORD-PICKUP');
});

test('driver page hides past orders', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-OLD', 'delivery_date' => today()->subDay()]);

    $response = withoutMiddleware(test()->driverMiddleware)->get(route('driver.index', [], false));

    $response->assertOk();
    $response->assertDontSee('ORD-OLD');
});

test('mark delivered changes order status', function () {
    $order = createDeliveryOrder(['status' => OrderStatus::Ready]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware(test()->driverMiddleware)
        ->post(route('driver.delivered', $order->order_number, false));

    $response->assertRedirect();
    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
});

test('mark delivered redirects back', function () {
    $order = createDeliveryOrder(['status' => OrderStatus::Ready]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware(test()->driverMiddleware)
        ->from(route('driver.index', [], false))
        ->post(route('driver.delivered', $order->order_number, false));

    $response->assertRedirect(route('driver.index', [], false));
});
