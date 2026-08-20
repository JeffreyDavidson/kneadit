<?php

use App\Filament\Pages\Tools\SmartShoppingList;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new SmartShoppingList;
});

test('start date defaults to today on mount', function () {
    testFixture('page', SmartShoppingList::class)->mount();

    expect(testFixture('page', SmartShoppingList::class)->startDate)->toBe(now()->format('Y-m-d'));
});

test('end date defaults to planning days ahead on mount', function () {
    testFixture('page', SmartShoppingList::class)->mount();

    $expected = now()->addDays(config('orders.default_planning_days', 7))->format('Y-m-d');
    expect(testFixture('page', SmartShoppingList::class)->endDate)->toBe($expected);
});

test('include upcoming defaults to false', function () {
    testFixture('page', SmartShoppingList::class)->mount();

    expect(testFixture('page', SmartShoppingList::class)->includeUpcoming)->toBeFalse();
});

test('mount initializes supplier groups', function () {
    testFixture('page', SmartShoppingList::class)->mount();

    expect(testFixture('page', SmartShoppingList::class)->supplierGroups)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('generate list populates supplier groups', function () {
    testFixture('page', SmartShoppingList::class)->mount();
    testFixture('page', SmartShoppingList::class)->generateList();

    expect(testFixture('page', SmartShoppingList::class)->supplierGroups)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('toggle upcoming flips flag and regenerates list', function () {
    testFixture('page', SmartShoppingList::class)->mount();

    expect(testFixture('page', SmartShoppingList::class)->includeUpcoming)->toBeFalse();

    testFixture('page', SmartShoppingList::class)->toggleUpcoming();

    expect(testFixture('page', SmartShoppingList::class)->includeUpcoming)->toBeTrue();

    testFixture('page', SmartShoppingList::class)->toggleUpcoming();

    expect(testFixture('page', SmartShoppingList::class)->includeUpcoming)->toBeFalse();
});
