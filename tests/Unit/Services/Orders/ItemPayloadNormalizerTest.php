<?php

use App\Services\Orders\ItemPayloadNormalizer;

test('normalizes product items and ignores malformed rows', function () {
    $items = resolve(ItemPayloadNormalizer::class)->products([
        ['product_id' => 10, 'quantity' => 2],
        ['product_id' => '11', 'quantity' => 1],
        ['product_id' => 12, 'quantity' => '3'],
        'invalid',
    ]);

    expect($items)->toBe([
        ['product_id' => 10, 'quantity' => 2],
    ]);
});

test('normalizes order item updates with their distinct identifier', function () {
    $items = resolve(ItemPayloadNormalizer::class)->orderItems([
        ['order_item_id' => 21, 'quantity' => 0],
        ['product_id' => 22, 'quantity' => 1],
    ]);

    expect($items)->toBe([
        ['order_item_id' => 21, 'quantity' => 0],
    ]);
});
