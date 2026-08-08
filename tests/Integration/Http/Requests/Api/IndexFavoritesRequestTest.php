<?php

use App\Http\Requests\Api\IndexFavoritesRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('email is required', function () {
    $validator = validator([], (new IndexFavoritesRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('email must be valid', function () {
    $validator = validator(['email' => 'not-email'], (new IndexFavoritesRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('valid email passes', function () {
    $validator = validator(['email' => 'fan@example.com'], (new IndexFavoritesRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
