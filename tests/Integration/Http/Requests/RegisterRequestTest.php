<?php

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('register request requires all fields', function () {
    foreach (['name', 'email', 'password', 'bakery_name', 'terms'] as $field) {
        $data = validRegistrationData();
        unset($data[$field]);

        $validator = validator($data, (new RegisterRequest)->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has($field))->toBeTrue();
    }
});

test('register request rejects weak passwords', function () {
    foreach ([
        'too short' => 'short1',
        'missing a letter' => '12345678',
        'missing a number' => 'allletters',
    ] as $password) {
        $data = array_merge(validRegistrationData(), [
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $validator = validator($data, (new RegisterRequest)->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    }
});

test('register request passes with valid data', function () {
    $request = new RegisterRequest;
    $validator = validator(validRegistrationData(), $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('register request rejects invalid email formats', function () {
    foreach (['not-an-email', 'jane@', '@example.com', 'jane example.com'] as $email) {
        $validator = validator(
            array_merge(validRegistrationData(), ['email' => $email]),
            (new RegisterRequest)->rules(),
        );

        expect($validator->errors()->has('email'))->toBeTrue();
    }
});

test('register request rejects falsy terms', function () {
    foreach ([false, 0, '0'] as $terms) {
        $validator = validator(
            array_merge(validRegistrationData(), ['terms' => $terms]),
            (new RegisterRequest)->rules(),
        );

        expect($validator->errors()->has('terms'))->toBeTrue();
    }
});

test('register request rejects values longer than 255 characters', function () {
    foreach ([
        'name' => str_repeat('a', 256),
        'email' => str_repeat('a', 250) . '@x.com',
        'bakery_name' => str_repeat('a', 256),
    ] as $field => $value) {
        $validator = validator(
            array_merge(validRegistrationData(), [$field => $value]),
            (new RegisterRequest)->rules(),
        );

        expect($validator->errors()->has($field))->toBeTrue();
    }
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
