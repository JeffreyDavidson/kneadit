<?php

use App\Http\Requests\Order\ApplyDiscountRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function () {
    $validator = validator([], (new ApplyDiscountRequest)->rules());

    foreach (['code', 'subtotal'] as $field) {
        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('code is capped at 50 chars', function () {
    $validator = validator(
        ['code' => str_repeat('A', 51), 'subtotal' => 10],
        (new ApplyDiscountRequest)->rules(),
    );

    expect($validator->errors()->has('code'))->toBeTrue();
});

test('subtotal must be numeric and non-negative', function () {
    foreach ([-1, 'free'] as $subtotal) {
        $validator = validator(
            ['code' => 'SAVE10', 'subtotal' => $subtotal],
            (new ApplyDiscountRequest)->rules(),
        );

        expect($validator->errors()->has('subtotal'))->toBeTrue();
    }
});

test('valid input passes', function () {
    $validator = validator(
        ['code' => 'SAVE10', 'subtotal' => 25.00],
        (new ApplyDiscountRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});
