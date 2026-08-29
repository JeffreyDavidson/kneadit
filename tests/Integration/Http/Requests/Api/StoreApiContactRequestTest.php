<?php

use App\Http\Requests\Api\StoreApiContactRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function () {
    $validator = validator([], (new StoreApiContactRequest)->rules());

    foreach (['name', 'email', 'subject', 'message'] as $field) {
        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('email must be valid', function () {
    $validator = validator(
        array_merge(validApiContactData(), ['email' => 'not-email']),
        (new StoreApiContactRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('message capped at 5000 chars', function () {
    $validator = validator(
        array_merge(validApiContactData(), ['message' => str_repeat('a', 5001)]),
        (new StoreApiContactRequest)->rules(),
    );

    expect($validator->errors()->has('message'))->toBeTrue();
});

test('valid contact passes', function () {
    $validator = validator(validApiContactData(), (new StoreApiContactRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validApiContactData(): array
{
    return [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'subject' => 'Question',
        'message' => 'Hi there',
    ];
}
