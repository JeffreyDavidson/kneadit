<?php

use App\Builders\OrderQueryBuilder;
use App\Models\Order;

it('returns a custom OrderQueryBuilder from Order::query()', function () {
    expect(Order::query())->toBeInstanceOf(OrderQueryBuilder::class);
});
