<?php

use App\Models\Orders\OrderItem;
use App\Presenters\OrderItemPresenter;
use App\ValueObjects\Money;

test('totalPrice() multiplies unit_price by quantity', function () {
    $item = new OrderItem(['quantity' => 3]);
    $item->unit_price = Money::fromDollars(5);

    expect(OrderItemPresenter::for($item)->totalPrice()->dollars())->toBe(15.00);
});

test('productName() falls back to the name saved on the line when the product is gone', function () {
    $item = new OrderItem(['name' => 'Old Loaf']);

    expect(OrderItemPresenter::for($item)->productName())->toBe('Old Loaf');
});

test('productName() falls back to a generic label when nothing is known', function () {
    expect(OrderItemPresenter::for(new OrderItem)->productName())->toBe('Product');
});
