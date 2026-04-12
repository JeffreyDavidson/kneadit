<?php

use App\Enums\Content\BlogPostCategory;
use App\Enums\Content\CaptionStyle;
use App\Enums\Content\PageType;
use App\Enums\Customers\ReferralStatus;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Financial\MarginHealth;
use App\Enums\Financial\PricingPosition;
use App\Enums\Inventory\StockAdjustmentType;
use App\Enums\Inventory\StockStatus;
use App\Enums\Orders\SenderType;
use App\Enums\Storefront\StorefrontTheme;

// ---------------------------------------------------------------------------
// Simple HasLabel enums — every case returns a non-empty label
// ---------------------------------------------------------------------------

dataset('simple_label_enums', function () {
    $enums = [
        CaptionStyle::class,
        PageType::class,
        ReferralStatus::class,
        CouponTransactionType::class,
        GiftCardTransactionType::class,
        StockAdjustmentType::class,
        StockStatus::class,
        SenderType::class,
        StorefrontTheme::class,
    ];

    foreach ($enums as $enum) {
        foreach ($enum::cases() as $case) {
            yield "{$enum}::{$case->name}" => [$case];
        }
    }
});

test('every case has a non-empty label', function ($case) {
    expect($case->getLabel())->toBeString()->not->toBeEmpty();
})->with('simple_label_enums');

// ---------------------------------------------------------------------------
// SenderType — custom boolean helpers
// ---------------------------------------------------------------------------

test('SenderType::isBaker returns true only for Baker', function () {
    expect(SenderType::Baker->isBaker())->toBeTrue()
        ->and(SenderType::Customer->isBaker())->toBeFalse();
});

test('SenderType::isCustomer returns true only for Customer', function () {
    expect(SenderType::Customer->isCustomer())->toBeTrue()
        ->and(SenderType::Baker->isCustomer())->toBeFalse();
});

// ---------------------------------------------------------------------------
// MarginHealth
// ---------------------------------------------------------------------------

test('MarginHealth has a non-empty label for every case', function (MarginHealth $case) {
    expect($case->getLabel())->toBeString()->not->toBeEmpty();
})->with(MarginHealth::cases());

test('MarginHealth::fromPercentage returns correct health', function (
    ?float $margin,
    MarginHealth $expected,
) {
    expect(MarginHealth::fromPercentage($margin))->toBe($expected);
})->with([
    'null returns Unknown' => [null, MarginHealth::Unknown],
    '50+ is Healthy' => [50.0, MarginHealth::Healthy],
    '75 is Healthy' => [75.0, MarginHealth::Healthy],
    '30 is Warning' => [30.0, MarginHealth::Warning],
    '49.9 is Warning' => [49.9, MarginHealth::Warning],
    '29.9 is Critical' => [29.9, MarginHealth::Critical],
    '0 is Critical' => [0.0, MarginHealth::Critical],
    'negative is Critical' => [-10.0, MarginHealth::Critical],
]);

test('MarginHealth::cssClass returns the backing value', function (MarginHealth $case) {
    expect($case->cssClass())->toBe($case->value);
})->with(MarginHealth::cases());

// ---------------------------------------------------------------------------
// PricingPosition
// ---------------------------------------------------------------------------

test('PricingPosition has a non-empty label for every case', function (PricingPosition $case) {
    expect($case->getLabel())->toBeString()->not->toBeEmpty();
})->with(PricingPosition::cases());

test('PricingPosition::multiplier returns expected values', function (
    PricingPosition $position,
    float $expected,
) {
    expect($position->multiplier())->toBe($expected);
})->with([
    'Economy' => [PricingPosition::Economy, 0.85],
    'Standard' => [PricingPosition::Standard, 1.0],
    'Premium' => [PricingPosition::Premium, 1.25],
]);

// ---------------------------------------------------------------------------
// BlogPostCategory
// ---------------------------------------------------------------------------

test('BlogPostCategory has a non-empty label for every case', function (BlogPostCategory $case) {
    expect($case->getLabel())->toBeString()->not->toBeEmpty();
})->with(BlogPostCategory::cases());

test('BlogPostCategory::options returns all cases plus All Posts', function () {
    $options = BlogPostCategory::options();

    expect($options)
        ->toHaveCount(count(BlogPostCategory::cases()) + 1)
        ->first()->toBe('All Posts');

    foreach (BlogPostCategory::cases() as $case) {
        expect($options)->toHaveKey($case->value);
    }
});

test('BlogPostCategory::color returns a non-empty string for every case', function (BlogPostCategory $case) {
    expect($case->color())->toBeString()->not->toBeEmpty();
})->with(BlogPostCategory::cases());
