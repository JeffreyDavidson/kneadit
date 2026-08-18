<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('redirects unauthenticated visitors to login', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/account/orders');

    $response->assertRedirect();
});

test('shows the customer their own orders', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->count(3)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get('/account/orders');

    $response->assertOk();
    $response->assertViewHas('orders', fn ($orders) => $orders->total() === 3);
});

test('does not leak orders belonging to another customer', function () {
    $me = Customer::factory()->create();
    $someoneElse = Customer::factory()->create();
    Order::factory()->for($me)->count(2)->create();
    Order::factory()->for($someoneElse)->count(5)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($me, 'customer')
        ->get('/account/orders');

    $response->assertOk();
    $response->assertViewHas('orders', fn ($orders) => $orders->total() === 2);
});

test('paginates at 20 orders per page', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->count(25)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get('/account/orders');

    $response->assertOk();
    $response->assertViewHas('orders', fn ($orders) => $orders->perPage() === 20 && $orders->total() === 25);
});
