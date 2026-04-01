<?php

use App\Models\Inventory\Product;
use App\Services\Financial\ProfitAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it returns product analysis with cost and margin data', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);

    $result = resolve(ProfitAnalysisService::class)->getProductAnalysis();

    expect($result)
        ->toHaveCount(1)
        ->first()->toMatchArray([
            'price' => 10.00,
            'cost' => 4.00,
            'margin_percentage' => 60.0,
            'margin_amount' => 6.0,
            'has_cost_data' => true,
            'color_class' => 'green',
        ]);
});

test('it calculates overall stats correctly', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 2.00]);
    Product::factory()->create(['price' => 10.00, 'cost' => 6.00]);
    Product::factory()->create(['price' => 10.00, 'cost' => null]);

    $stats = resolve(ProfitAnalysisService::class)->getOverallStats();

    expect($stats)
        ->total_products->toBe(3)
        ->products_with_costs->toBe(2)
        ->products_missing_costs->toBe(1);
});

test('it returns top 5 profitable products', function () {
    for ($i = 1; $i <= 7; $i++) {
        Product::factory()->create(['price' => 10.00, 'cost' => 10.00 - $i]);
    }

    $result = resolve(ProfitAnalysisService::class)->getTopProfitableProducts();

    expect($result)->toHaveCount(5);
});

test('it calculates total revenue potential', function () {
    Product::factory()->create(['price' => 10.00, 'cost' => 4.00]);
    Product::factory()->create(['price' => 20.00, 'cost' => 8.00]);

    $result = resolve(ProfitAnalysisService::class)->getTotalRevenuePotential();

    expect($result)
        ->total_revenue_potential->toBe(30.0)
        ->total_costs->toBe(12.0)
        ->total_profit_potential->toBe(18.0);
});
