<?php

use App\Http\Requests\Storefront\StoreOrderMessageRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = [
        'message' => 'Hello, when can I pick up?',
        'sender_name' => 'Alice',
        'sender_email' => 'alice@example.com',
    ];
    unset($data[$field]);

    $validator = validator($data, (new StoreOrderMessageRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['message', 'sender_name', 'sender_email']);

test('sender_email must be valid', function () {
    $validator = validator([
        'message' => 'Hi',
        'sender_name' => 'Alice',
        'sender_email' => 'not-email',
    ], (new StoreOrderMessageRequest)->rules());

    expect($validator->errors()->has('sender_email'))->toBeTrue();
});

test('message capped at 2000 chars', function () {
    $validator = validator([
        'message' => str_repeat('a', 2001),
        'sender_name' => 'Alice',
        'sender_email' => 'alice@example.com',
    ], (new StoreOrderMessageRequest)->rules());

    expect($validator->errors()->has('message'))->toBeTrue();
});

test('valid message passes', function () {
    $validator = validator([
        'message' => 'When can I pick up?',
        'sender_name' => 'Alice',
        'sender_email' => 'alice@example.com',
    ], (new StoreOrderMessageRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
