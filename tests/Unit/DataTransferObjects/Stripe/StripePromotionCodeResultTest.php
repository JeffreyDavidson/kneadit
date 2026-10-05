<?php

use App\DataTransferObjects\Stripe\StripePromotionCodeResult;

test('it round-trips through livewire', function () {
    $result = new StripePromotionCodeResult(promotionCodeId: 'promo_1', code: 'SUMMER25', couponId: 'coupon_1');

    $restored = StripePromotionCodeResult::fromLivewire($result->toLivewire());

    expect($restored)->toEqual($result);
});

test('it rejects a malformed livewire payload', function (mixed $payload) {
    StripePromotionCodeResult::fromLivewire($payload);
})->with([
    'not an array' => 'nope',
    'missing code' => [['promotionCodeId' => 'promo_1', 'couponId' => 'coupon_1']],
    'non-string id' => [['promotionCodeId' => 1, 'code' => 'A', 'couponId' => 'coupon_1']],
])->throws(UnexpectedValueException::class);
