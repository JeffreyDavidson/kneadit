<?php

use App\Builders\Orders\OrderQueryBuilder;
use App\Models\Orders\Order;

it('returns a custom OrderQueryBuilder from Order::query()', function () {
    expect(Order::query())->toBeInstanceOf(OrderQueryBuilder::class);
});
