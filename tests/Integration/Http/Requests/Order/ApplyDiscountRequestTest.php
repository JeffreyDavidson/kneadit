<?php

use App\Http\Requests\Order\ApplyDiscountRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = ['code' => 'SAVE10', 'subtotal' => 25.00];
    unset($data[$field]);

    $validator = validator($data, (new ApplyDiscountRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['code', 'subtotal']);

test('code is capped at 50 chars', function () {
    $validator = validator(
        ['code' => str_repeat('A', 51), 'subtotal' => 10],
        (new ApplyDiscountRequest)->rules(),
    );

    expect($validator->errors()->has('code'))->toBeTrue();
});

test('subtotal must be numeric and non-negative', function (mixed $subtotal) {
    $validator = validator(
        ['code' => 'SAVE10', 'subtotal' => $subtotal],
        (new ApplyDiscountRequest)->rules(),
    );

    expect($validator->errors()->has('subtotal'))->toBeTrue();
})->with([
    'negative' => -1,
    'string' => 'free',
]);

test('valid input passes', function () {
    $validator = validator(
        ['code' => 'SAVE10', 'subtotal' => 25.00],
        (new ApplyDiscountRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});
