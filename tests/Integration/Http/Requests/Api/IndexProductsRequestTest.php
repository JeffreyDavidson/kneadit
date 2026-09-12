<?php

use App\Http\Requests\Api\IndexProductsRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('all params optional', function () {
    $validator = validator([], (new IndexProductsRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('featured accepts boolean-shaped string values', function () {
    foreach (['true', 'false', '1', '0'] as $value) {
        $validator = validator(['featured' => $value], (new IndexProductsRequest)->rules());

        expect($validator->passes())->toBeTrue();
    }
});

test('featured rejects other strings', function () {
    $validator = validator(['featured' => 'yes'], (new IndexProductsRequest)->rules());

    expect($validator->errors()->has('featured'))->toBeTrue();
});
