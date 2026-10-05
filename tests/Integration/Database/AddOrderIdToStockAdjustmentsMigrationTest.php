<?php

use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function runAddOrderIdToStockAdjustmentsMigration(): void
{
    $migration = require database_path('migrations/tenant/2026_10_05_120000_add_order_id_to_stock_adjustments_table.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function insertRawAdjustment(Ingredient $ingredient, string $type, ?string $notes): int
{
    return DB::table('stock_adjustments')->insertGetId([
        'ingredient_id' => $ingredient->id,
        'quantity' => -1,
        'type' => $type,
        'notes' => $notes,
    ]);
}

test('links earlier usage and restock rows to their order by the order number in the notes', function () {
    $ingredient = Ingredient::factory()->create();
    $order = Order::factory()->create(['order_number' => 'ORD-5001']);
    $usage = insertRawAdjustment($ingredient, 'usage', 'Order #ORD-5001');
    $restock = insertRawAdjustment($ingredient, 'restock', 'Order #ORD-5001 cancelled');

    runAddOrderIdToStockAdjustmentsMigration();

    expect(DB::table('stock_adjustments')->where('id', $usage)->value('order_id'))->toBe($order->id)
        ->and(DB::table('stock_adjustments')->where('id', $restock)->value('order_id'))->toBe($order->id);
});

test('leaves manual adjustments and rows naming an unknown order unlinked', function () {
    $ingredient = Ingredient::factory()->create();
    $manual = insertRawAdjustment($ingredient, 'waste', 'Order #ORD-5002 dropped');
    $unknown = insertRawAdjustment($ingredient, 'usage', 'Order #ORD-MISSING');
    $noNotes = insertRawAdjustment($ingredient, 'usage', null);
    Order::factory()->create(['order_number' => 'ORD-5002']);

    runAddOrderIdToStockAdjustmentsMigration();

    expect(DB::table('stock_adjustments')->where('id', $manual)->value('order_id'))->toBeNull()
        ->and(DB::table('stock_adjustments')->where('id', $unknown)->value('order_id'))->toBeNull()
        ->and(DB::table('stock_adjustments')->where('id', $noNotes)->value('order_id'))->toBeNull();
});

test('running the migration again changes nothing', function () {
    $ingredient = Ingredient::factory()->create();
    $order = Order::factory()->create(['order_number' => 'ORD-5003']);
    $usage = insertRawAdjustment($ingredient, 'usage', 'Order #ORD-5003');

    runAddOrderIdToStockAdjustmentsMigration();
    runAddOrderIdToStockAdjustmentsMigration();

    expect(DB::table('stock_adjustments')->where('id', $usage)->value('order_id'))->toBe($order->id);
});
