<?php

use App\Filament\Pages\Operations\DeliveryRoutePlanner;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new DeliveryRoutePlanner;
});

test('mount sets selected date to today', function () {
    test()->page->mount();

    expect(test()->page->selectedDate)->toBe(now()->format('Y-m-d'));
});

test('mount loads store address', function () {
    test()->page->mount();

    expect(test()->page->storeAddress)->toBeString();
});

test('mount loads orders', function () {
    test()->page->mount();

    expect(test()->page->deliveryOrders)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('load orders with null date sets empty collection', function () {
    test()->page->selectedDate = null;

    test()->page->loadOrders();

    expect(test()->page->deliveryOrders)->toBeEmpty();
});

test('load orders with valid date populates collection', function () {
    test()->page->selectedDate = now()->format('Y-m-d');

    test()->page->loadOrders();

    expect(test()->page->deliveryOrders)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get route stats returns array', function () {
    test()->page->mount();

    expect(test()->page->getRouteStats())->toBeArray();
});
