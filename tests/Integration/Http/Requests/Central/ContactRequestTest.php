<?php

use App\Http\Requests\Central\ContactRequest;

beforeEach(fn () => setUpCentralTest());

test('required fields are enforced', function () {
    $validator = validator([], (new ContactRequest)->rules());

    foreach (['name', 'email', 'message'] as $field) {
        expect($validator->errors()->has($field))->toBeTrue();
    }
});

test('email must be valid', function () {
    $validator = validator([
        'name' => 'Alice',
        'email' => 'not-email',
        'message' => 'Hi there',
    ], (new ContactRequest)->rules());

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('message capped at 5000 chars', function () {
    $validator = validator([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'message' => str_repeat('a', 5001),
    ], (new ContactRequest)->rules());

    expect($validator->errors()->has('message'))->toBeTrue();
});

test('valid contact passes', function () {
    $validator = validator([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'message' => 'Interested in your service.',
    ], (new ContactRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
