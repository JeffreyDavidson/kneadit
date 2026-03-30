<?php

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    $this->driverMiddleware = [
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

    $response = withoutMiddleware($this->driverMiddleware)->get('/driver');

    $response->assertOk();
});

test('driver page shows todays delivery orders', function () {
    settings(['store_name' => 'Test Bakery']);
    $order = createDeliveryOrder(['order_number' => 'ORD-001']);

    $response = withoutMiddleware($this->driverMiddleware)->get('/driver');

    $response->assertOk();
    $response->assertSee('ORD-001');
});

test('driver page hides pickup orders', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-PICKUP', 'delivery_address' => '']);

    $response = withoutMiddleware($this->driverMiddleware)->get('/driver');

    $response->assertOk();
    $response->assertDontSee('ORD-PICKUP');
});

test('driver page hides past orders', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-OLD', 'delivery_date' => today()->subDay()]);

    $response = withoutMiddleware($this->driverMiddleware)->get('/driver');

    $response->assertOk();
    $response->assertDontSee('ORD-OLD');
});

test('mark delivered changes order status', function () {
    $order = createDeliveryOrder(['status' => OrderStatus::Ready]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware($this->driverMiddleware)
        ->post("/driver/{$order->order_number}/delivered");

    $response->assertRedirect();
    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
});

test('mark delivered redirects back', function () {
    $order = createDeliveryOrder(['status' => OrderStatus::Ready]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware($this->driverMiddleware)
        ->from('/driver')
        ->post("/driver/{$order->order_number}/delivered");

    $response->assertRedirect('/driver');
});
