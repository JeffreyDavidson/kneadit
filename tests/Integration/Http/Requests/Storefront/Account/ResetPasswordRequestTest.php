<?php

use App\Http\Requests\Storefront\Account\ResetPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = validResetPasswordData();
    unset($data[$field]);

    $validator = validator($data, (new ResetPasswordRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['token', 'email', 'password']);

test('email must be valid', function () {
    $validator = validator(
        array_merge(validResetPasswordData(), ['email' => 'not-email']),
        (new ResetPasswordRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('password must meet strength rules (>= 8 chars, letters + numbers)', function (string $password) {
    $validator = validator(
        array_merge(validResetPasswordData(), [
            'password' => $password,
            'password_confirmation' => $password,
        ]),
        (new ResetPasswordRequest)->rules(),
    );

    expect($validator->errors()->has('password'))->toBeTrue();
})->with([
    'too short' => 'Ab1',
    'no numbers' => 'Abcdefgh',
    'no letters' => '12345678',
]);

test('password confirmation must match', function () {
    $validator = validator(
        array_merge(validResetPasswordData(), [
            'password' => 'Strong1Pass',
            'password_confirmation' => 'Different1',
        ]),
        (new ResetPasswordRequest)->rules(),
    );

    expect($validator->errors()->has('password'))->toBeTrue();
});

test('valid input passes', function () {
    $validator = validator(validResetPasswordData(), (new ResetPasswordRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validResetPasswordData(): array
{
    return [
        'token' => 'reset-token-abc123',
        'email' => 'customer@example.com',
        'password' => 'Strong1Pass',
        'password_confirmation' => 'Strong1Pass',
    ];
}
