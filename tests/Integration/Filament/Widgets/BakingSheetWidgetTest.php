<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\BakingSheetWidget;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
});

test('bakes the bakery-local day as today', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create(['delivery_date' => '2026-10-05', 'status' => OrderStatus::Pending]);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 3]);

    $canView = BakingSheetWidget::canView();
    $rows = (new BakingSheetWidget)->getRows();

    expect($canView)->toBeTrue()
        ->and($rows)->toHaveCount(1)
        ->and($rows[0]['quantity'])->toBe(3);
});

test('includes confirmed orders after the bakery-local day but not pending ones', function () {
    $product = Product::factory()->create();
    $confirmed = Order::factory()->create(['delivery_date' => '2026-10-06', 'status' => OrderStatus::Confirmed]);
    $pending = Order::factory()->create(['delivery_date' => '2026-10-06', 'status' => OrderStatus::Pending]);
    OrderItem::factory()->recycle($confirmed, $product)->create(['quantity' => 2]);
    OrderItem::factory()->recycle($pending, $product)->create(['quantity' => 5]);

    $rows = (new BakingSheetWidget)->getRows();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['quantity'])->toBe(2);
});
