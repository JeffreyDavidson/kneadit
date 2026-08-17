<?php

use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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

test('register request rejects an invalid email format', function (string $bad) {
    $validator = validator(
        array_merge(validRegistrationData(), ['email' => $bad]),
        (new RegisterRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
})->with(['not-an-email', 'jane@', '@example.com', 'jane example.com']);

test('register request rejects a password missing a letter', function () {
    $validator = validator(
        array_merge(validRegistrationData(), ['password' => '12345678', 'password_confirmation' => '12345678']),
        (new RegisterRequest)->rules(),
    );

    expect($validator->errors()->has('password'))->toBeTrue();
});

test('register request rejects a password missing a number', function () {
    $validator = validator(
        array_merge(validRegistrationData(), ['password' => 'allletters', 'password_confirmation' => 'allletters']),
        (new RegisterRequest)->rules(),
    );

    expect($validator->errors()->has('password'))->toBeTrue();
});

test('register request rejects terms set to a falsy value (not just missing)', function (mixed $rejected) {
    $validator = validator(
        array_merge(validRegistrationData(), ['terms' => $rejected]),
        (new RegisterRequest)->rules(),
    );

    expect($validator->errors()->has('terms'))->toBeTrue();
})->with([false, 0, '0']);

test('register request rejects values longer than 255 chars', function (string $field, string $value) {
    $validator = validator(
        array_merge(validRegistrationData(), [$field => $value]),
        (new RegisterRequest)->rules(),
    );

    expect($validator->errors()->has($field))->toBeTrue();
})->with([
    'name too long' => ['name', str_repeat('a', 256)],
    'email too long' => ['email', str_repeat('a', 250) . '@x.com'],
    'bakery name too long' => ['bakery_name', str_repeat('a', 256)],
]);

/** @return array<string, mixed> */
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
