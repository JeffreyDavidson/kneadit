<?php

use App\Http\Requests\Storefront\UpdateCustomerProfileRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('name is required', function () {
    $validator = validator([], (new UpdateCustomerProfileRequest)->rules());

    expect($validator->errors()->has('name'))->toBeTrue();
});

test('birthday must be in the past', function () {
    $validator = validator([
        'name' => 'Alice',
        'birthday' => now()->addDay()->toDateString(),
    ], (new UpdateCustomerProfileRequest)->rules());

    expect($validator->errors()->has('birthday'))->toBeTrue();
});

test('birthday must be a valid date', function () {
    $validator = validator([
        'name' => 'Alice',
        'birthday' => 'not-a-date',
    ], (new UpdateCustomerProfileRequest)->rules());

    expect($validator->errors()->has('birthday'))->toBeTrue();
});

test('valid update passes', function () {
    $validator = validator([
        'name' => 'Alice Customer',
        'phone' => '555-0100',
        'birthday' => '1990-06-15',
        'address' => '123 Main St',
        'city' => 'Bakerstown',
        'state' => 'CA',
        'zip' => '94000',
    ], (new UpdateCustomerProfileRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('minimal update with only name passes', function () {
    $validator = validator([
        'name' => 'Alice',
    ], (new UpdateCustomerProfileRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
