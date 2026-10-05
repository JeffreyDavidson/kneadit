<?php

use App\Enums\Engagement\LoyaltyPointType;

test('has expected cases', function () {
    expect(LoyaltyPointType::cases())
        ->toHaveCount(4)
        ->and(LoyaltyPointType::Earned->value)->toBe('earned')
        ->and(LoyaltyPointType::Redeemed->value)->toBe('redeemed')
        ->and(LoyaltyPointType::Adjusted->value)->toBe('adjusted')
        ->and(LoyaltyPointType::Reversed->value)->toBe('reversed');
});

test('textClass returns the Tailwind text color', function (LoyaltyPointType $type, string $class) {
    expect($type->textClass())->toBe($class);
})->with([
    'earned' => [LoyaltyPointType::Earned, 'text-green-600'],
    'redeemed' => [LoyaltyPointType::Redeemed, 'text-red-600'],
    'adjusted' => [LoyaltyPointType::Adjusted, 'text-yellow-600'],
    'reversed' => [LoyaltyPointType::Reversed, 'text-red-600'],
]);

test('formatPoints prefixes the sign the type implies', function (LoyaltyPointType $type, int $points, string $expected) {
    expect($type->formatPoints($points))->toBe($expected);
})->with([
    'earned' => [LoyaltyPointType::Earned, 1250, '+1,250'],
    'redeemed is stored positive' => [LoyaltyPointType::Redeemed, 1250, '-1,250'],
    'reversed is stored positive' => [LoyaltyPointType::Reversed, 1250, '-1,250'],
    'positive adjustment' => [LoyaltyPointType::Adjusted, 50, '+50'],
    'negative adjustment' => [LoyaltyPointType::Adjusted, -50, '-50'],
    'zero adjustment' => [LoyaltyPointType::Adjusted, 0, '+0'],
]);
