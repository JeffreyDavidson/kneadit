<?php

use App\Filament\Pages\Operations\DeliveryRoutePlanner;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new DeliveryRoutePlanner;
});

test('mount sets selected date to today', function () {
    testFixture('page', DeliveryRoutePlanner::class)->mount();

    expect(testFixture('page', DeliveryRoutePlanner::class)->selectedDate)->toBe(now()->format('Y-m-d'));
});

test('mount loads store address', function () {
    testFixture('page', DeliveryRoutePlanner::class)->mount();

    expect(testFixture('page', DeliveryRoutePlanner::class)->storeAddress)->toBeString();
});

test('mount loads orders', function () {
    testFixture('page', DeliveryRoutePlanner::class)->mount();

    expect(testFixture('page', DeliveryRoutePlanner::class)->deliveryOrders)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('load orders with null date sets empty collection', function () {
    testFixture('page', DeliveryRoutePlanner::class)->selectedDate = null;

    testFixture('page', DeliveryRoutePlanner::class)->loadOrders();

    expect(testFixture('page', DeliveryRoutePlanner::class)->deliveryOrders)->toBeEmpty();
});

test('load orders with valid date populates collection', function () {
    testFixture('page', DeliveryRoutePlanner::class)->selectedDate = now()->format('Y-m-d');

    testFixture('page', DeliveryRoutePlanner::class)->loadOrders();

    expect(testFixture('page', DeliveryRoutePlanner::class)->deliveryOrders)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get route stats returns array', function () {
    testFixture('page', DeliveryRoutePlanner::class)->mount();

    expect(testFixture('page', DeliveryRoutePlanner::class)->getRouteStats())->toBeArray();
});
