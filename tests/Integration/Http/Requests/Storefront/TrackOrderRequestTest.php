<?php

use App\Http\Requests\Storefront\TrackOrderRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('email is required', function () {
    $validator = validator([], (new TrackOrderRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

test('email must be a valid format', function () {
    $validator = validator(['email' => 'not-an-email'], (new TrackOrderRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('valid email passes', function () {
    $validator = validator(['email' => 'customer@example.com'], (new TrackOrderRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
