<?php

use App\Filament\Widgets\TopProductsWidget;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    // 10:00 pm on Oct 31 in New York, which is already November in UTC.
    Date::setTestNow('2026-11-01 02:00');
});

test('ranks the products delivered on the last bakery-local day of the month', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);
    $order = Order::factory()->paid()->create(['delivery_date' => '2026-10-31', 'created_at' => '2026-10-31 14:00:00']);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 10]);

    $products = (new TopProductsWidget)->getProducts();

    expect($products)->toHaveCount(1)
        ->and($products[0]['name'])->toBe('Sourdough');
});

test('leaves out products delivered on the first day of the next bakery-local month', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->paid()->create(['delivery_date' => '2026-11-01']);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 10]);

    $products = (new TopProductsWidget)->getProducts();

    expect($products)->toBeEmpty();
});
