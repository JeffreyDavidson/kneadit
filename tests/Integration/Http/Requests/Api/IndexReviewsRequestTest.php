<?php

use App\Http\Requests\Api\IndexReviewsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('all params optional', function () {
    $validator = validator([], (new IndexReviewsRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('featured accepts boolean-shaped values', function () {
    foreach (['true', 'false', '1', '0'] as $value) {
        $validator = validator(['featured' => $value], (new IndexReviewsRequest)->rules());

        expect($validator->passes())->toBeTrue();
    }
});

test('product_id must be an integer', function () {
    $validator = validator(['product_id' => 'abc'], (new IndexReviewsRequest)->rules());

    expect($validator->errors()->has('product_id'))->toBeTrue();
});
