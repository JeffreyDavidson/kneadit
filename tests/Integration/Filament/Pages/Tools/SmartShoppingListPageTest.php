<?php

use App\Filament\Pages\Tools\SmartShoppingList;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new SmartShoppingList;
});

test('start date defaults to today on mount', function () {
    test()->page->mount();

    expect(test()->page->startDate)->toBe(now()->format('Y-m-d'));
});

test('end date defaults to planning days ahead on mount', function () {
    test()->page->mount();

    $expected = now()->addDays(config('orders.default_planning_days', 7))->format('Y-m-d');
    expect(test()->page->endDate)->toBe($expected);
});

test('include upcoming defaults to false', function () {
    test()->page->mount();

    expect(test()->page->includeUpcoming)->toBeFalse();
});

test('mount initializes supplier groups', function () {
    test()->page->mount();

    expect(test()->page->supplierGroups)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('generate list populates supplier groups', function () {
    test()->page->mount();
    test()->page->generateList();

    expect(test()->page->supplierGroups)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('toggle upcoming flips flag and regenerates list', function () {
    test()->page->mount();

    expect(test()->page->includeUpcoming)->toBeFalse();

    test()->page->toggleUpcoming();

    expect(test()->page->includeUpcoming)->toBeTrue();

    test()->page->toggleUpcoming();

    expect(test()->page->includeUpcoming)->toBeFalse();
});
