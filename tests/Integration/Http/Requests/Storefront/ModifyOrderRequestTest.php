<?php

use App\Http\Requests\Storefront\ModifyOrderRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('items is required when missing', function () {
    $validator = validator([], (new ModifyOrderRequest)->rules());

    expect($validator->errors()->has('items'))->toBeTrue();
});

test('items must contain at least one row', function () {
    $validator = validator(['items' => []], (new ModifyOrderRequest)->rules());

    expect($validator->errors()->has('items'))->toBeTrue();
});

test('each item requires order_item_id and quantity', function () {
    $validator = validator(['items' => [[]]], (new ModifyOrderRequest)->rules());

    foreach (['order_item_id', 'quantity'] as $field) {
        expect($validator->errors()->has("items.0.{$field}"))->toBeTrue();
    }
});

test('item quantity is bounded 0..20', function () {
    foreach ([-1, 21, 100] as $quantity) {
        $validator = validator([
            'items' => [['order_item_id' => 5, 'quantity' => $quantity]],
        ], (new ModifyOrderRequest)->rules());

        expect($validator->errors()->has('items.0.quantity'))->toBeTrue();
    }
});

test('quantity 0 is allowed (treated as removal)', function () {
    $validator = validator([
        'items' => [['order_item_id' => 5, 'quantity' => 0]],
    ], (new ModifyOrderRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('tip_amount is bounded 0..1000', function () {
    foreach ([-1, 1001, 'abc'] as $tip) {
        $validator = validator([
            'items' => [['order_item_id' => 5, 'quantity' => 1]],
            'tip_amount' => $tip,
        ], (new ModifyOrderRequest)->rules());

        expect($validator->errors()->has('tip_amount'))->toBeTrue();
    }
});

test('valid modification passes', function () {
    $validator = validator([
        'items' => [
            ['order_item_id' => 5, 'quantity' => 2],
            ['order_item_id' => 7, 'quantity' => 0],
        ],
        'tip_amount' => 5.00,
    ], (new ModifyOrderRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
