<?php

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('tracking page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.track', [], false));

    $response->assertOk();
});

test('tracking lookup returns orders for email', function () {
    $customer = Customer::factory()->create(['email' => 'tracker@example.com']);
    Order::factory()->recycle($customer)->count(2)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.track.lookup', [], false), [
            'email' => 'tracker@example.com',
        ]);

    $response->assertOk()
        ->assertViewHas('orders');
});
