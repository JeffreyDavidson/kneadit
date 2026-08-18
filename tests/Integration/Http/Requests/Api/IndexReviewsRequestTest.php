<?php

use App\Http\Requests\Api\IndexReviewsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('all params optional', function () {
    $validator = validator([], (new IndexReviewsRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('featured accepts boolean-shaped values', function (string $value) {
    $validator = validator(['featured' => $value], (new IndexReviewsRequest)->rules());

    expect($validator->passes())->toBeTrue();
})->with(['true', 'false', '1', '0']);

test('product_id must be an integer', function () {
    $validator = validator(['product_id' => 'abc'], (new IndexReviewsRequest)->rules());

    expect($validator->errors()->has('product_id'))->toBeTrue();
});
