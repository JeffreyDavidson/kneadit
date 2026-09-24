<?php

use App\DataTransferObjects\Inventory\InventoryReportResult;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\StockAdjustment;
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

test('calculates recent usage from stock adjustments and offsets restocks', function () {
    Config::set('analytics.inventory_usage_window_days', 10);

    $ingredient = Ingredient::factory()->create();

    StockAdjustment::factory()->for($ingredient)->create([
        'quantity' => -10,
        'type' => StockAdjustmentType::Usage,
        'created_at' => now()->subDay(),
    ]);
    StockAdjustment::factory()->for($ingredient)->create([
        'quantity' => 2,
        'type' => StockAdjustmentType::Restock,
        'created_at' => now()->subDay(),
    ]);
    StockAdjustment::factory()->for($ingredient)->create([
        'quantity' => 100,
        'type' => StockAdjustmentType::Purchase,
        'created_at' => now()->subDay(),
    ]);
    StockAdjustment::factory()->for($ingredient)->create([
        'quantity' => -100,
        'type' => StockAdjustmentType::Waste,
        'created_at' => now()->subDay(),
    ]);
    StockAdjustment::factory()->for($ingredient)->create([
        'quantity' => -90,
        'type' => StockAdjustmentType::Usage,
        'created_at' => now()->subDays(11),
    ]);
    StockAdjustment::factory()->for($ingredient)->create([
        'quantity' => -90,
        'type' => StockAdjustmentType::Usage,
        'created_at' => now()->addDay(),
    ]);

    $result = (new InventoryReport)->generate();
    $reportedIngredient = collect($result->ingredients)->firstWhere('name', $ingredient->name);

    expect($reportedIngredient->dailyUsage)->toBe(0.8);
});
