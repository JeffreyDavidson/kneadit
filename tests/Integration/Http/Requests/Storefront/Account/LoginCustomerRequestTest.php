<?php

use App\Http\Requests\Storefront\Account\LoginCustomerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('email and password are required', function (string $field) {
    $data = ['email' => 'a@b.com', 'password' => 'secret'];
    unset($data[$field]);

    $validator = validator($data, (new LoginCustomerRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['email', 'password']);

test('email must be valid', function () {
    $validator = validator(
        ['email' => 'not-email', 'password' => 'secret'],
        (new LoginCustomerRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('valid credentials shape passes', function () {
    $validator = validator(
        ['email' => 'alice@example.com', 'password' => 'whatever'],
        (new LoginCustomerRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});
