<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Queries\Orders\BakingSheetQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('returns empty collection for date with no orders', function () {
    $items = BakingSheetQuery::forDate('2099-12-31');

    expect($items)->toBeEmpty();
});

test('returns aggregated baking items for a given date', function () {
    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['name' => 'Sourdough']);
    $date = now()->format('Y-m-d');

    $order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->confirmed()
        ->create(['delivery_date' => $date]);

    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 10.00,
    ]);

    $items = BakingSheetQuery::forDate($date);

    expect($items)->toHaveCount(1)
        ->and($items->first()->product_name)->toBe('Sourdough')
        ->and((int) $items->first()->total_quantity)->toBe(3);
});
