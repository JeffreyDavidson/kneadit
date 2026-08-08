<?php

use App\Http\Requests\Order\RedeemLoyaltyRewardRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('email is required', function () {
    $validator = validator([], (new RedeemLoyaltyRewardRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('email must be valid', function () {
    $validator = validator(['email' => 'not-email'], (new RedeemLoyaltyRewardRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('valid email passes', function () {
    $validator = validator(['email' => 'customer@example.com'], (new RedeemLoyaltyRewardRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
