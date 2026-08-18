<?php

use App\Http\Requests\Storefront\StoreContactMessageRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('all fields are required', function (string $field) {
    $data = validContactMessageData();
    unset($data[$field]);

    $validator = validator($data, (new StoreContactMessageRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['name', 'email', 'subject', 'message']);

test('email must be valid', function () {
    $validator = validator(
        array_merge(validContactMessageData(), ['email' => 'not-email']),
        (new StoreContactMessageRequest)->rules(),
    );

    expect($validator->errors()->has('email'))->toBeTrue();
});

test('message is capped at 2000 characters', function () {
    $validator = validator(
        array_merge(validContactMessageData(), ['message' => str_repeat('a', 2001)]),
        (new StoreContactMessageRequest)->rules(),
    );

    expect($validator->errors()->has('message'))->toBeTrue();
});

test('valid input passes', function () {
    $validator = validator(validContactMessageData(), (new StoreContactMessageRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

function validContactMessageData(): array
{
    return [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'subject' => 'Question about delivery',
        'message' => 'Do you deliver on Sundays?',
    ];
}
