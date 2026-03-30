<?php

use App\Services\PricingCalculator;

test('calculates recommended pricing', function () {
    $calculator = new PricingCalculator;

    $result = $calculator->calculate(
        ingredientCost: 5.00,
        prepTimeMinutes: 30,
        hourlyLaborRate: 20.00,
        overheadPercentage: 15,
        targetProfitMargin: 40,
    );

    expect($result)
        ->toBeArray()
        ->toHaveKeys(['ingredient_cost', 'labor_cost', 'overhead', 'total_cost', 'recommended_price', 'min_price', 'max_price', 'profit_per_unit', 'actual_margin', 'bulk'])
        ->and($result['ingredient_cost'])->toBe(5.0)
        ->and($result['labor_cost'])->toBe(10.0)
        ->and($result['total_cost'])->toBeGreaterThan(0)
        ->and($result['recommended_price'])->toBeGreaterThan($result['total_cost'])
        ->and($result['actual_margin'])->toBeGreaterThan(0);
});

test('premium positioning increases price', function () {
    $calculator = new PricingCalculator;

    $standard = $calculator->calculate(5.00, 30, 20.00, 15, 40, 'standard');
    $premium = $calculator->calculate(5.00, 30, 20.00, 15, 40, 'premium');

    expect($premium['recommended_price'])->toBeGreaterThan($standard['recommended_price']);
});

test('bulk pricing offers discounts', function () {
    $calculator = new PricingCalculator;

    $result = $calculator->calculate(5.00, 30, 20.00, 15, 40);

    expect($result['bulk'])->toHaveCount(2)
        ->and($result['bulk'][0]['unit_price'])->toBeLessThan($result['recommended_price'])
        ->and($result['bulk'][1]['unit_price'])->toBeLessThan($result['bulk'][0]['unit_price']);
});
