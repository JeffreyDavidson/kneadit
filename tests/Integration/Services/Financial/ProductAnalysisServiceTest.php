<?php

use App\DataTransferObjects\Financial\ProductCostAnalysis;
use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Financial\MarginHealth;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Services\Financial\ProductAnalysisService;

beforeEach(fn () => setUpTenantTest());

// --- analyze() ---

test('analyze returns ProductCostAnalysis with cost and margin', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => 5.00]);

    $analysis = resolve(ProductAnalysisService::class)->analyze($product);

    expect($analysis)
        ->toBeInstanceOf(ProductCostAnalysis::class)
        ->cost->toBe(5.0)
        ->price->toBe(20.0)
        ->currentMarginPercent->toBe(75.0)
        ->profitPerUnit->toBe(15.0)
        ->marginHealth->toBe(MarginHealth::Healthy);
});

test('analyze falls back to recipe cost when product cost is null', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => null]);
    Recipe::factory()->for($product)->create(['cost' => 8.00]);

    $analysis = resolve(ProductAnalysisService::class)->analyze($product);

    expect($analysis->cost)->toBe(8.0);
});

test('analyze falls back to ingredients JSON when no cost fields set', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => null]);
    Recipe::factory()->for($product)->create([
        'cost' => null,
        'ingredients' => [
            ['name' => 'Flour', 'quantity' => 2, 'unit' => 'cups', 'cost' => 0.50],
            ['name' => 'Sugar', 'quantity' => 1, 'unit' => 'cup', 'cost' => 0.75],
        ],
    ]);

    $analysis = resolve(ProductAnalysisService::class)->analyze($product);

    expect($analysis->cost)->toBe(1.75)
        ->and($analysis->ingredients)->toHaveCount(2);
});

test('analyze calculates suggested price from target margin', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => 5.00]);

    $analysis = resolve(ProductAnalysisService::class)->analyze($product, 60.0);

    expect($analysis->suggestedPrice)->toBe(12.5);
});

test('analyze returns Unknown margin health when no cost data', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => null]);

    $analysis = resolve(ProductAnalysisService::class)->analyze($product);

    expect($analysis->marginHealth)->toBe(MarginHealth::Unknown)
        ->and($analysis->currentMarginPercent)->toBeNull();
});

// --- portfolio() ---

test('portfolio returns ProductPortfolioSummary', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);

    $portfolio = resolve(ProductAnalysisService::class)->portfolio();

    expect($portfolio)
        ->toBeInstanceOf(ProductPortfolioSummary::class)
        ->totalProducts->toBe(1)
        ->productsWithCosts->toBe(1)
        ->and($portfolio->products->first())->toMatchArray([
            'price' => 10.00,
            'cost' => 4.00,
            'margin_percentage' => 60.0,
            'margin_amount' => 6.0,
            'has_cost_data' => true,
            'color_class' => 'green',
        ]);
});

test('portfolio calculates overall stats', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 2.00]);
    Product::factory()->create(['price' => 10.00, 'cost' => 6.00]);
    Product::factory()->create(['price' => 10.00, 'cost' => null]);

    $portfolio = resolve(ProductAnalysisService::class)->portfolio();

    expect($portfolio)
        ->totalProducts->toBe(3)
        ->productsWithCosts->toBe(2)
        ->and($portfolio->productsMissingCosts())->toBe(1);
});

test('portfolio top profitable returns top 5', function () {
    for ($i = 1; $i <= 7; $i++) {
        Product::factory()->create(['price' => 10.00, 'cost' => 10.00 - $i]);
    }

    $portfolio = resolve(ProductAnalysisService::class)->portfolio();

    expect($portfolio->topProfitable())->toHaveCount(5);
});

test('portfolio calculates total revenue potential', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);
    Product::factory()->create(['price' => 20.00, 'cost' => 8.00]);

    $portfolio = resolve(ProductAnalysisService::class)->portfolio();

    expect($portfolio)
        ->totalRevenuePotential->toBe(30.0)
        ->totalCosts->toBe(12.0)
        ->totalProfitPotential->toBe(18.0);
});
