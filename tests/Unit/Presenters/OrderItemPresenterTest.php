<?php

use App\Models\Orders\OrderItem;
use App\Presenters\OrderItemPresenter;

test('totalPrice() multiplies unit_price by quantity', function () {
    $item = new OrderItem(['quantity' => 3]);
    $item->unit_price = 5.00;

    expect(OrderItemPresenter::for($item)->totalPrice()->dollars())->toBe(15.00);
});
