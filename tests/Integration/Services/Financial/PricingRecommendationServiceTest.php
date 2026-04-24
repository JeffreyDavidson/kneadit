<?php

use App\DataTransferObjects\Financial\PricingRecommendation;
use App\Enums\Financial\PricingPosition;
use App\Services\Financial\PricingRecommendationService;

beforeEach(fn () => setUpTenantTest());

// --- recommend() ---

test('recommend returns PricingRecommendation', function () {
    $result = resolve(PricingRecommendationService::class)->recommend(
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
    $service = resolve(PricingRecommendationService::class);

    $standard = $service->recommend(5.00, 30, 20.00, 15, 40, PricingPosition::Standard);
    $premium = $service->recommend(5.00, 30, 20.00, 15, 40, PricingPosition::Premium);

    expect($premium->recommendedPrice)->toBeGreaterThan($standard->recommendedPrice);
});

test('bulk tiers offer discounts', function () {
    $result = resolve(PricingRecommendationService::class)->recommend(5.00, 30, 20.00, 15, 40);

    expect($result->bulkTiers)->toHaveCount(2)
        ->and($result->bulkTiers[0]['unit_price'])->toBeLessThan($result->recommendedPrice)
        ->and($result->bulkTiers[1]['unit_price'])->toBeLessThan($result->bulkTiers[0]['unit_price']);
});

// --- suggestPrice() ---

test('suggestPrice calculates from cost and target margin', function () {
    $price = resolve(PricingRecommendationService::class)->suggestPrice(5.00, 60.0);

    expect($price)->toBe(12.5);
});

test('suggestPrice returns zero for zero cost', function () {
    $price = resolve(PricingRecommendationService::class)->suggestPrice(0, 60.0);

    expect($price)->toBe(0.0);
});
