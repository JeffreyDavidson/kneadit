<?php

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Orders\Order;
use App\Queries\Customers\CustomerDirectoryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('search returns matching customers with order metrics', function () {
    $customer = Customer::factory()->create(['name' => 'Ada Bakery']);
    Customer::factory()->create(['name' => 'Other Customer']);
    Order::factory()->recycle($customer)->create([
        'total' => 25,
        'created_at' => now()->subDay(),
    ]);

    $customers = CustomerDirectoryQuery::search('Ada');
    $result = $customers->sole();

    expect($result->is($customer))->toBeTrue()
        ->and($result->orders_count)->toBe(1)
        ->and($result->last_order_date)->not->toBeNull();
});

test('findWithDetails loads orders and customer notes', function () {
    $customer = Customer::factory()->create();
    Order::factory()->recycle($customer)->create();
    CustomerNote::factory()->recycle($customer)->create();

    $result = CustomerDirectoryQuery::findWithDetails($customer->id);

    expect($result)->not->toBeNull()
        ->and($result?->relationLoaded('orders'))->toBeTrue()
        ->and($result?->relationLoaded('customerNotes'))->toBeTrue();
});
