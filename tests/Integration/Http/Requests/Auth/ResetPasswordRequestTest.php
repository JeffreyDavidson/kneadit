<?php

use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function () {
    foreach (['token', 'email', 'password'] as $field) {
        $data = validAuthResetPasswordData();
        unset($data[$field]);

        $validator = validator($data, (new ResetPasswordRequest)->rules());

        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('email must be valid', function () {
    $validator = validator(
        array_merge(validAuthResetPasswordData(), ['email' => 'not-email']),
        (new ResetPasswordRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('password must meet strength rules (>= 8 chars, letters + numbers)', function () {
    foreach (['Ab1', 'Abcdefgh', '12345678'] as $password) {
        $validator = validator(
            array_merge(validAuthResetPasswordData(), [
                'password' => $password,
                'password_confirmation' => $password,
            ]),
            (new ResetPasswordRequest)->rules(),
        );

        expect($validator->errors()->has('password'))->toBeTrue();
    }
});

test('password confirmation must match', function () {
    $validator = validator(
        array_merge(validAuthResetPasswordData(), [
            'password' => 'Strong1Pass',
            'password_confirmation' => 'Different1',
        ]),
        (new ResetPasswordRequest)->rules(),
    );

    expect($validator->errors()->has('password'))->toBeTrue();
});

test('valid input passes', function () {
    $validator = validator(validAuthResetPasswordData(), (new ResetPasswordRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validAuthResetPasswordData(): array
{
    return [
        'token' => 'admin-reset-token-xyz',
        'email' => 'admin@example.com',
        'password' => 'Strong1Pass',
        'password_confirmation' => 'Strong1Pass',
    ];
}
