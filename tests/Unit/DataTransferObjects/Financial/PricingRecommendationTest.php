<?php

use App\DataTransferObjects\Financial\PricingRecommendation;

test('PricingRecommendation can be constructed and properties are accessible', function () {
    $dto = new PricingRecommendation(
        ingredientCost: 2.50,
        laborCost: 1.00,
        overhead: 0.50,
        totalCost: 4.00,
        recommendedPrice: 8.00,
        minPrice: 6.00,
        maxPrice: 10.00,
        currentPrice: 7.50,
        profitPerUnit: 3.50,
        actualMarginPercent: 53.3,
        bulkTiers: [
            ['qty' => 6, 'label' => 'Half dozen', 'unit_price' => 7.50, 'total' => 45.00],
        ],
    );

    expect($dto)
        ->ingredientCost->toBe(2.50)
        ->laborCost->toBe(1.00)
        ->overhead->toBe(0.50)
        ->totalCost->toBe(4.00)
        ->recommendedPrice->toBe(8.00)
        ->minPrice->toBe(6.00)
        ->maxPrice->toBe(10.00)
        ->currentPrice->toBe(7.50)
        ->profitPerUnit->toBe(3.50)
        ->actualMarginPercent->toBe(53.3)
        ->bulkTiers->toHaveCount(1);
});

test('PricingRecommendation toLivewire returns all properties as array', function () {
    $dto = new PricingRecommendation(
        ingredientCost: 2.50,
        laborCost: 1.00,
        overhead: 0.50,
        totalCost: 4.00,
        recommendedPrice: 8.00,
        minPrice: 6.00,
        maxPrice: 10.00,
        currentPrice: 7.50,
        profitPerUnit: 3.50,
        actualMarginPercent: 53.3,
        bulkTiers: [],
    );

    $wire = $dto->toLivewire();

    expect($wire)
        ->toBeArray()
        ->ingredientCost->toBe(2.50)
        ->laborCost->toBe(1.00)
        ->overhead->toBe(0.50)
        ->totalCost->toBe(4.00)
        ->recommendedPrice->toBe(8.00)
        ->minPrice->toBe(6.00)
        ->maxPrice->toBe(10.00)
        ->currentPrice->toBe(7.50)
        ->profitPerUnit->toBe(3.50)
        ->actualMarginPercent->toBe(53.3)
        ->bulkTiers->toBeEmpty();
});

test('PricingRecommendation fromLivewire reconstructs DTO from array', function () {
    $data = [
        'ingredientCost' => 2.50,
        'laborCost' => 1.00,
        'overhead' => 0.50,
        'totalCost' => 4.00,
        'recommendedPrice' => 8.00,
        'minPrice' => 6.00,
        'maxPrice' => 10.00,
        'currentPrice' => 7.50,
        'profitPerUnit' => 3.50,
        'actualMarginPercent' => 53.3,
        'bulkTiers' => [
            ['qty' => 12, 'label' => 'Dozen', 'unit_price' => 7.00, 'total' => 84.00],
        ],
    ];

    $dto = PricingRecommendation::fromLivewire($data);

    expect($dto)
        ->toBeInstanceOf(PricingRecommendation::class)
        ->ingredientCost->toBe(2.50)
        ->laborCost->toBe(1.00)
        ->currentPrice->toBe(7.50)
        ->bulkTiers->toHaveCount(1);
});

test('PricingRecommendation fromLivewire handles null currentPrice', function () {
    $data = [
        'ingredientCost' => 1.0,
        'laborCost' => 1.0,
        'overhead' => 1.0,
        'totalCost' => 3.0,
        'recommendedPrice' => 6.0,
        'minPrice' => 4.0,
        'maxPrice' => 8.0,
        'profitPerUnit' => 3.0,
        'actualMarginPercent' => 50.0,
        'bulkTiers' => [],
    ];

    $dto = PricingRecommendation::fromLivewire($data);

    expect($dto->currentPrice)->toBeNull();
});

test('PricingRecommendation round-trips through toLivewire and fromLivewire', function () {
    $original = new PricingRecommendation(
        ingredientCost: 3.25,
        laborCost: 2.10,
        overhead: 0.75,
        totalCost: 6.10,
        recommendedPrice: 12.00,
        minPrice: 9.00,
        maxPrice: 15.00,
        currentPrice: null,
        profitPerUnit: 5.90,
        actualMarginPercent: 49.2,
        bulkTiers: [
            ['qty' => 6, 'label' => 'Half dozen', 'unit_price' => 11.00, 'total' => 66.00],
        ],
    );

    $restored = PricingRecommendation::fromLivewire($original->toLivewire());

    expect($restored)
        ->ingredientCost->toBe($original->ingredientCost)
        ->laborCost->toBe($original->laborCost)
        ->overhead->toBe($original->overhead)
        ->totalCost->toBe($original->totalCost)
        ->recommendedPrice->toBe($original->recommendedPrice)
        ->minPrice->toBe($original->minPrice)
        ->maxPrice->toBe($original->maxPrice)
        ->currentPrice->toBe($original->currentPrice)
        ->profitPerUnit->toBe($original->profitPerUnit)
        ->actualMarginPercent->toBe($original->actualMarginPercent)
        ->bulkTiers->toBe($original->bulkTiers);
});
