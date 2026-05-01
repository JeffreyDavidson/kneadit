<?php

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('email is required', function () {
    $validator = validator([], (new ForgotPasswordRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('email must be valid', function () {
    $validator = validator(['email' => 'not-email'], (new ForgotPasswordRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('valid email passes', function () {
    $validator = validator(['email' => 'admin@example.com'], (new ForgotPasswordRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
