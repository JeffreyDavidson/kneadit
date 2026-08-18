<?php

use App\Http\Requests\Order\PurchaseGiftCardRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('required fields are enforced', function (string $field) {
    $data = validPurchaseGiftCardData();
    unset($data[$field]);

    $validator = validator($data, (new PurchaseGiftCardRequest)->rules());

    expect($validator->errors()->has($field))->toBeTrue();
})->with(['purchaser_name', 'purchaser_email', 'initial_balance']);

test('purchaser_email must be valid', function () {
    $validator = validator(
        array_merge(validPurchaseGiftCardData(), ['purchaser_email' => 'bad']),
        (new PurchaseGiftCardRequest)->rules(),
    );

    expect($validator->errors()->has('purchaser_email'))->toBeTrue();
});

test('recipient_email must be valid when provided', function () {
    $validator = validator(
        array_merge(validPurchaseGiftCardData(), ['recipient_email' => 'bad']),
        (new PurchaseGiftCardRequest)->rules(),
    );

    expect($validator->errors()->has('recipient_email'))->toBeTrue();
});

test('initial_balance must be between 1 and 500', function (mixed $amount) {
    $validator = validator(
        array_merge(validPurchaseGiftCardData(), ['initial_balance' => $amount]),
        (new PurchaseGiftCardRequest)->rules(),
    );

    expect($validator->errors()->has('initial_balance'))->toBeTrue();
})->with([
    'zero' => 0,
    'too-large' => 501,
    'negative' => -10,
]);

test('message capped at 1000 chars', function () {
    $validator = validator(
        array_merge(validPurchaseGiftCardData(), ['message' => str_repeat('a', 1001)]),
        (new PurchaseGiftCardRequest)->rules(),
    );

    expect($validator->errors()->has('message'))->toBeTrue();
});

test('valid purchase passes', function () {
    $validator = validator(validPurchaseGiftCardData(), (new PurchaseGiftCardRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('valid purchase with optional recipient passes', function () {
    $validator = validator(
        array_merge(validPurchaseGiftCardData(), [
            'recipient_name' => 'Bob',
            'recipient_email' => 'bob@example.com',
            'message' => 'Happy birthday!',
        ]),
        (new PurchaseGiftCardRequest)->rules(),
    );

    expect($validator->passes())->toBeTrue();
});

function validPurchaseGiftCardData(): array
{
    return [
        'purchaser_name' => 'Alice',
        'purchaser_email' => 'alice@example.com',
        'initial_balance' => 50,
    ];
}
