<?php

use App\Enums\Content\BlogPostCategory;
use App\Enums\Content\CaptionStyle;
use App\Enums\Content\PageType;
use App\Enums\Customers\ReferralStatus;
use App\Enums\Engagement\SurveyQuestionType;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Financial\MarginHealth;
use App\Enums\Financial\PricingPosition;
use App\Enums\Inventory\StockAdjustmentType;
use App\Enums\Inventory\StockStatus;
use App\Enums\Operations\ActivityAction;
use App\Enums\Orders\SenderType;
use App\Enums\Platform\DnsVerificationStatus;
use App\Enums\Storefront\StorefrontTheme;

// ---------------------------------------------------------------------------
// Simple HasLabel enums — every case returns a non-empty label
// ---------------------------------------------------------------------------

dataset('simple_label_enums', [
    'ActivityAction' => [ActivityAction::class],
    'CaptionStyle' => [CaptionStyle::class],
    'DnsVerificationStatus' => [DnsVerificationStatus::class],
    'PageType' => [PageType::class],
    'ReferralStatus' => [ReferralStatus::class],
    'CouponTransactionType' => [CouponTransactionType::class],
    'GiftCardTransactionType' => [GiftCardTransactionType::class],
    'StockAdjustmentType' => [StockAdjustmentType::class],
    'StockStatus' => [StockStatus::class],
    'SenderType' => [SenderType::class],
    'StorefrontTheme' => [StorefrontTheme::class],
    'SurveyQuestionType' => [SurveyQuestionType::class],
]);

test('every case has a non-empty label', function (string $enum) {
    foreach ($enum::cases() as $case) {
        expect($case->getLabel())->toBeString()->not->toBeEmpty();
    }
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

test('MarginHealth has a non-empty label for every case', function () {
    foreach (MarginHealth::cases() as $case) {
        expect($case->getLabel())->toBeString()->not->toBeEmpty();
    }
});

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

test('MarginHealth::cssClass returns the backing value', function () {
    foreach (MarginHealth::cases() as $case) {
        expect($case->cssClass())->toBe($case->value);
    }
});

// ---------------------------------------------------------------------------
// PricingPosition
// ---------------------------------------------------------------------------

test('PricingPosition has a non-empty label for every case', function () {
    foreach (PricingPosition::cases() as $case) {
        expect($case->getLabel())->toBeString()->not->toBeEmpty();
    }
});

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

test('BlogPostCategory has a non-empty label for every case', function () {
    foreach (BlogPostCategory::cases() as $case) {
        expect($case->getLabel())->toBeString()->not->toBeEmpty();
    }
});

test('BlogPostCategory::options returns all cases plus All Posts', function () {
    $options = BlogPostCategory::options();

    expect($options)
        ->toHaveCount(count(BlogPostCategory::cases()) + 1);

    expect($options->first())->toBe('All Posts');
});

test('BlogPostCategory::options includes every case', function () {
    $options = BlogPostCategory::options();

    foreach (BlogPostCategory::cases() as $case) {
        expect($options)->toHaveKey($case->value);
    }
});

test('BlogPostCategory::getColor returns a non-empty string for every case', function () {
    foreach (BlogPostCategory::cases() as $case) {
        expect($case->getColor())->toBeString()->not->toBeEmpty();
    }
});

// ---------------------------------------------------------------------------
// ActivityAction — every case must have a getColor()
// ---------------------------------------------------------------------------

test('ActivityAction::getColor returns a non-empty string for every case', function () {
    foreach (ActivityAction::cases() as $case) {
        expect($case->getColor())->toBeString()->not->toBeEmpty();
    }
});

// ---------------------------------------------------------------------------
// DnsVerificationStatus — every case must have a getColor()
// ---------------------------------------------------------------------------

test('DnsVerificationStatus::getColor returns a non-empty string for every case', function () {
    foreach (DnsVerificationStatus::cases() as $case) {
        expect($case->getColor())->toBeString()->not->toBeEmpty();
    }
});
