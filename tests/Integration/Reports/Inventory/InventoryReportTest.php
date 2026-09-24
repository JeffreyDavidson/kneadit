<?php

use App\DataTransferObjects\Inventory\InventoryReportResult;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Reports\Inventory\InventoryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates inventory report', function () {
    $report = new InventoryReport;
    $result = $report->generate();

    expect($result)->toBeInstanceOf(InventoryReportResult::class)
        ->and($result->ingredients)->toBeEmpty()
        ->and($result->totalItems)->toBe(0)
        ->and($result->toArray()['ingredients'])->toBeEmpty();
});

test('excludes cancelled paid orders from recent ingredient usage', function () {
    Config::set('analytics.inventory_usage_window_days', 10);

    $ingredient = Ingredient::factory()->create();
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $recipe->inventoryIngredients()->attach($ingredient, ['quantity' => 2.5, 'unit' => 'kg']);

    $deliveredOrder = Order::factory()->delivered()->create([
        'delivery_date' => now()->subDays(2),
    ]);
    OrderItem::factory()->recycle($deliveredOrder, $product)->create(['quantity' => 3]);

    $cancelledOrder = Order::factory()->cancelled()->paid()->create([
        'delivery_date' => now()->subDays(1),
    ]);
    OrderItem::factory()->recycle($cancelledOrder, $product)->create(['quantity' => 11]);

    $result = (new InventoryReport)->generate();
    $reportedIngredient = collect($result->ingredients)->firstWhere('name', $ingredient->name);

    expect($reportedIngredient->dailyUsage)->toBe(0.75);
});
