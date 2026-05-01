<?php

use App\Http\Requests\Central\ContactRequest;

beforeEach(fn () => setUpCentralTest());

test('required fields are enforced', function (string $field) {
    $data = [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'message' => 'Hi there',
    ];
    unset($data[$field]);

    $validator = validator($data, (new ContactRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['name', 'email', 'message']);

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
