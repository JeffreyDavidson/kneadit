<?php

use App\Http\Requests\Order\CheckCapacityRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('date is required', function () {
    $validator = validator([], (new CheckCapacityRequest)->rules());

    expect($validator->errors()->has('date'))->toBeTrue();
});

test('date must be parseable', function () {
    $validator = validator(['date' => 'not-a-date'], (new CheckCapacityRequest)->rules());

    expect($validator->errors()->has('date'))->toBeTrue();
});

test('valid date passes', function () {
    $validator = validator(['date' => '2026-12-25'], (new CheckCapacityRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
