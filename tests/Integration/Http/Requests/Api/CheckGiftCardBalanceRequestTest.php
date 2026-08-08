<?php

use App\Http\Requests\Api\CheckGiftCardBalanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('code is required', function () {
    $validator = validator([], (new CheckGiftCardBalanceRequest)->rules());

    expect($validator->errors()->has('code'))->toBeTrue();
});

test('valid code passes', function () {
    $validator = validator(['code' => 'GIFT-12345'], (new CheckGiftCardBalanceRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
