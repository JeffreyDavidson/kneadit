<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function runTenantMigration(string $file): void
{
    $migration = require database_path("migrations/tenant/{$file}");

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

/** @return array<string, string> */
function schemaSnapshot(): array
{
    return DB::table('sqlite_master')
        ->whereNotNull('sql')
        ->orderBy('name')
        ->pluck('sql', 'name')
        ->all();
}

function foreignKeyAction(string $table, string $column): ?string
{
    foreach (Schema::getForeignKeys($table) as $foreignKey) {
        if ($foreignKey['columns'] === [$column]) {
            return $foreignKey['on_delete'];
        }
    }

    return null;
}

test('deleting a product row leaves its order lines on the order, with the product unlinked', function () {
    $order = Order::factory()->create();
    $product = Product::factory()->create();
    $line = OrderItem::factory()->for($order)->for($product)->create(['name' => 'Sourdough Loaf']);

    DB::table('products')->where('id', $product->id)->delete();

    $line = $line->fresh();

    expect(OrderItem::query()->count())->toBe(1)
        ->and($line?->product_id)->toBeNull()
        ->and($line?->name)->toBe('Sourdough Loaf')
        ->and($line?->order_id)->toBe($order->id);
});

test('order lines still go when their own order is deleted', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create();

    DB::table('orders')->where('id', $order->id)->delete();

    expect(OrderItem::query()->count())->toBe(0);
});

test('a category that still has products cannot be deleted at the database level', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create();

    expect(fn () => DB::table('categories')->where('id', $category->id)->delete())
        ->toThrow(QueryException::class)
        ->and(Product::query()->count())->toBe(1);
});

test('the foreign keys use the intended delete rules', function () {
    expect(foreignKeyAction('order_items', 'product_id'))->toBe('set null')
        ->and(foreignKeyAction('order_items', 'order_id'))->toBe('cascade')
        ->and(foreignKeyAction('products', 'category_id'))->toBe('restrict');
});

test('rebuilding the tables keeps every index, check constraint and other table definition', function () {
    $before = schemaSnapshot();

    runTenantMigration('2026_10_06_120000_protect_order_items_from_product_deletion.php');
    runTenantMigration('2026_10_06_120100_restrict_category_deletion_with_products.php');

    $after = schemaSnapshot();

    $unrebuilt = fn (array $schema): array => array_diff_key($schema, array_flip(['order_items', 'products']));

    expect($unrebuilt($after))->toEqual($unrebuilt($before))
        ->and(array_keys($after))->toEqual(array_keys($before))
        ->and(foreignKeyAction('order_items', 'product_id'))->toBe('set null')
        ->and(foreignKeyAction('products', 'category_id'))->toBe('restrict');
});

test('rebuilding order_items keeps its rows', function () {
    OrderItem::factory()->count(3)->create();

    runTenantMigration('2026_10_06_120000_protect_order_items_from_product_deletion.php');

    expect(OrderItem::query()->count())->toBe(3)
        ->and(OrderItem::query()->whereNull('product_id')->count())->toBe(0);
});

test('lines recorded before names were copied onto order lines get the product name', function () {
    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);
    $unnamed = OrderItem::factory()->for($product)->create(['name' => null]);
    $named = OrderItem::factory()->for($product)->create(['name' => 'Catering loaf']);
    $custom = OrderItem::factory()->create(['product_id' => null, 'name' => 'Custom cake']);

    runTenantMigration('2026_10_06_120000_protect_order_items_from_product_deletion.php');

    expect($unnamed->fresh()?->name)->toBe('Sourdough Loaf')
        ->and($named->fresh()?->name)->toBe('Catering loaf')
        ->and($custom->fresh()?->name)->toBe('Custom cake');
});

dataset('foreignKeyIndexes', [
    'order_items.order_id' => ['order_items', 'order_id'],
    'refunds.order_id' => ['refunds', 'order_id'],
    'cart_items.cart_id' => ['cart_items', 'cart_id'],
    'reviews.product_id' => ['reviews', 'product_id'],
    'recipes.product_id' => ['recipes', 'product_id'],
    'stock_adjustments.ingredient_id' => ['stock_adjustments', 'ingredient_id'],
    'customer_notes.customer_id' => ['customer_notes', 'customer_id'],
    'orders.coupon_id' => ['orders', 'coupon_id'],
    'orders.gift_card_id' => ['orders', 'gift_card_id'],
    'survey_responses.order_id' => ['survey_responses', 'order_id'],
]);

test('hot foreign key columns are indexed', function (string $table, string $column) {
    expect(Schema::hasIndex($table, [$column]))->toBeTrue();
})->with('foreignKeyIndexes');

test('the plain customers email index is gone but the unique one that serves the same lookups stays', function () {
    expect(Schema::hasIndex('customers', 'customers_email_index'))->toBeFalse()
        ->and(Schema::hasIndex('customers', ['email'], 'unique'))->toBeTrue();
});

test('adding the indexes again is harmless', function () {
    $before = schemaSnapshot();

    runTenantMigration('2026_10_06_120200_add_missing_foreign_key_indexes_for_order_history.php');

    expect(schemaSnapshot())->toEqual($before);
});
