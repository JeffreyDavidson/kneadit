<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Queries\Orders\BakingSheetQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
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
    $item = $items->first();
    if (! $item instanceof OrderItem) {
        throw new UnexpectedValueException('The baking sheet item is missing.');
    }
    $attributes = $item->getAttributes();

    expect($items)->toHaveCount(1)
        ->and(Arr::string($attributes, 'product_name'))->toBe('Sourdough')
        ->and(Arr::integer($attributes, 'total_quantity'))->toBe(3);
});

test('dashboard rows aggregate todays active orders and future confirmed orders', function () {
    $product = Product::factory()->create(['name' => 'Country Loaf']);
    $todayPending = Order::factory()->pending()->create(['delivery_date' => today()]);
    $futureConfirmed = Order::factory()->confirmed()->create(['delivery_date' => today()->addDay()]);
    $futurePending = Order::factory()->pending()->create(['delivery_date' => today()->addDay()]);

    OrderItem::factory()->recycle($todayPending, $product)->create(['quantity' => 2]);
    OrderItem::factory()->recycle($futureConfirmed, $product)->create(['quantity' => 3]);
    OrderItem::factory()->recycle($futurePending, $product)->create(['quantity' => 10]);

    expect(BakingSheetQuery::hasDashboardItems())->toBeTrue()
        ->and(BakingSheetQuery::forDashboard())->toBe([
            ['product_id' => $product->id, 'name' => 'Country Loaf', 'quantity' => 5],
        ]);
});
