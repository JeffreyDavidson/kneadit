<?php

use App\Http\Requests\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('register request requires all fields', function (string $field) {
    $request = new RegisterRequest;
    $data = validRegistrationData();
    unset($data[$field]);

    $validator = validator($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has($field))->toBeTrue();
})->with(['name', 'email', 'password', 'bakery_name', 'terms']);

test('register request rejects weak passwords', function (string $password) {
    $request = new RegisterRequest;
    $data = array_merge(validRegistrationData(), [
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $validator = validator($data, $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
})->with(['short1', '12345678']);

test('register request passes with valid data', function () {
    $request = new RegisterRequest;
    $validator = validator(validRegistrationData(), $request->rules());

    expect($validator->passes())->toBeTrue();
});

function validRegistrationData(): array
{
    return [
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'Secret123',
        'password_confirmation' => 'Secret123',
        'bakery_name' => 'Jane\'s Bakery',
        'terms' => true,
    ];
}
