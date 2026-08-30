<?php

use App\Http\Requests\Storefront\Account\RegisterCustomerRequest;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function () {
    foreach (['name', 'email', 'password'] as $field) {
        $data = validRegisterCustomerData();
        unset($data[$field]);

        $validator = validator($data, (new RegisterCustomerRequest)->rules());

        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('password must be at least 8 chars and contain letters and numbers', function () {
    foreach (['Ab1', 'Abcdefgh', '12345678'] as $password) {
        $validator = validator(
            array_merge(validRegisterCustomerData(), [
                'password' => $password,
                'password_confirmation' => $password,
            ]),
            (new RegisterCustomerRequest)->rules(),
        );

        expect($validator->errors()->has('password'))->toBeTrue();
    }
});

test('email already used by an account with a password is rejected', function () {
    Customer::factory()->create([
        'email' => 'taken@example.com',
        'password' => bcrypt('existing-secret'),
    ]);

    $validator = validator(
        array_merge(validRegisterCustomerData(), ['email' => 'taken@example.com']),
        (new RegisterCustomerRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('email already used by a guest customer (no password) is allowed — registration claims it', function () {
    Customer::factory()->create([
        'email' => 'guest@example.com',
        'password' => null,
    ]);

    $validator = validator(
        array_merge(validRegisterCustomerData(), ['email' => 'guest@example.com']),
        (new RegisterCustomerRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});

test('valid input passes', function () {
    $validator = validator(validRegisterCustomerData(), (new RegisterCustomerRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validRegisterCustomerData(): array
{
    return [
        'name' => 'Alice Customer',
        'email' => 'alice@example.com',
        'phone' => '555-1234',
        'password' => 'Strong1Pass',
        'password_confirmation' => 'Strong1Pass',
    ];
}
