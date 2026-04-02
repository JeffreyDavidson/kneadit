<?php

use App\DataTransferObjects\Financial\PricingRecommendation;
use App\DataTransferObjects\Financial\ProductCostAnalysis;
use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Financial\MarginHealth;
use App\Enums\Financial\PricingPosition;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Services\Financial\ProductFinancialService;

beforeEach(fn () => setUpTenantTest());

// --- analyze() ---

test('analyze returns ProductCostAnalysis with cost and margin', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => 5.00]);

    $analysis = resolve(ProductFinancialService::class)->analyze($product);

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

    $analysis = resolve(ProductFinancialService::class)->analyze($product);

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

    $analysis = resolve(ProductFinancialService::class)->analyze($product);

    expect($analysis->cost)->toBe(1.75);
    expect($analysis->ingredients)->toHaveCount(2);
});

test('analyze calculates suggested price from target margin', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => 5.00]);

    $analysis = resolve(ProductFinancialService::class)->analyze($product, 60.0);

    expect($analysis->suggestedPrice)->toBe(12.5);
});

test('analyze returns Unknown margin health when no cost data', function () {
    $product = Product::factory()->create(['price' => 20.00, 'cost' => null]);

    $analysis = resolve(ProductFinancialService::class)->analyze($product);

    expect($analysis->marginHealth)->toBe(MarginHealth::Unknown);
    expect($analysis->currentMarginPercent)->toBeNull();
});

// --- recommend() ---

test('recommend returns PricingRecommendation', function () {
    $result = resolve(ProductFinancialService::class)->recommend(
        ingredientCost: 5.00,
        prepTimeMinutes: 30,
        hourlyLaborRate: 20.00,
        overheadPercentage: 15,
        targetMarginPercent: 40,
    );

    expect($result)
        ->toBeInstanceOf(PricingRecommendation::class)
        ->ingredientCost->toBe(5.0)
        ->laborCost->toBe(10.0)
        ->totalCost->toBeGreaterThan(0.0)
        ->recommendedPrice->toBeGreaterThan($result->totalCost)
        ->actualMarginPercent->toBeGreaterThan(0.0);
});

test('premium positioning increases recommended price', function () {
    $service = resolve(ProductFinancialService::class);

    $standard = $service->recommend(5.00, 30, 20.00, 15, 40, PricingPosition::Standard);
    $premium = $service->recommend(5.00, 30, 20.00, 15, 40, PricingPosition::Premium);

    expect($premium->recommendedPrice)->toBeGreaterThan($standard->recommendedPrice);
});

test('bulk tiers offer discounts', function () {
    $result = resolve(ProductFinancialService::class)->recommend(5.00, 30, 20.00, 15, 40);

    expect($result->bulkTiers)->toHaveCount(2);
    expect($result->bulkTiers[0]['unit_price'])->toBeLessThan($result->recommendedPrice);
    expect($result->bulkTiers[1]['unit_price'])->toBeLessThan($result->bulkTiers[0]['unit_price']);
});

// --- portfolio() ---

test('portfolio returns ProductPortfolioSummary', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);

    $portfolio = resolve(ProductFinancialService::class)->portfolio();

    expect($portfolio)
        ->toBeInstanceOf(ProductPortfolioSummary::class)
        ->totalProducts->toBe(1)
        ->productsWithCosts->toBe(1);

    expect($portfolio->products->first())
        ->toMatchArray([
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

    $portfolio = resolve(ProductFinancialService::class)->portfolio();

    expect($portfolio)
        ->totalProducts->toBe(3)
        ->productsWithCosts->toBe(2);
    expect($portfolio->productsMissingCosts())->toBe(1);
});

test('portfolio top profitable returns top 5', function () {
    for ($i = 1; $i <= 7; $i++) {
        Product::factory()->create(['price' => 10.00, 'cost' => 10.00 - $i]);
    }

    $portfolio = resolve(ProductFinancialService::class)->portfolio();

    expect($portfolio->topProfitable())->toHaveCount(5);
});

test('portfolio calculates total revenue potential', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);
    Product::factory()->create(['price' => 20.00, 'cost' => 8.00]);

    $portfolio = resolve(ProductFinancialService::class)->portfolio();

    expect($portfolio)
        ->totalRevenuePotential->toBe(30.0)
        ->totalCosts->toBe(12.0)
        ->totalProfitPotential->toBe(18.0);
});

// --- suggestPrice() ---

test('suggestPrice calculates from cost and target margin', function () {
    $price = resolve(ProductFinancialService::class)->suggestPrice(5.00, 60.0);

    expect($price)->toBe(12.5);
});

test('suggestPrice returns zero for zero cost', function () {
    $price = resolve(ProductFinancialService::class)->suggestPrice(0, 60.0);

    expect($price)->toBe(0.0);
});
