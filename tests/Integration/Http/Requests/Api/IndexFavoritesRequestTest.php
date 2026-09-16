<?php

use App\Http\Requests\Api\IndexFavoritesRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('does not accept an email identity from the request', function () {
    $validator = validator([], (new IndexFavoritesRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('request email does not affect validation', function () {
    $validator = validator(['email' => 'not-email'], (new IndexFavoritesRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('request remains valid with extra input', function () {
    $validator = validator(['email' => 'fan@example.com'], (new IndexFavoritesRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
