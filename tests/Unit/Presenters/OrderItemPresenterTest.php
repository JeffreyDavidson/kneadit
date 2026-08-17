<?php

use App\Models\Orders\OrderItem;
use App\Presenters\OrderItemPresenter;
use App\ValueObjects\Money;

test('totalPrice() multiplies unit_price by quantity', function () {
    $item = new OrderItem(['quantity' => 3]);
    $item->unit_price = Money::fromDollars(5);

    expect(OrderItemPresenter::for($item)->totalPrice()->dollars())->toBe(15.00);
});
