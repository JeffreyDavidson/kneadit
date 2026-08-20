<?php

use App\Filament\Pages\Tools\ShoppingListGenerator;
use App\Services\Orders\OrderIngredientAggregator;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ShoppingListGenerator;
});

test('mount sets start date to today', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();

    expect(testFixture('page', ShoppingListGenerator::class)->startDate)->toBe(now()->format('Y-m-d'));
});

test('mount sets end date to planning days ahead', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();

    $expectedEnd = now()->addDays(config('orders.default_planning_days', 7))->format('Y-m-d');
    expect(testFixture('page', ShoppingListGenerator::class)->endDate)->toBe($expectedEnd);
});

test('mount initializes empty shopping list', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();

    expect(testFixture('page', ShoppingListGenerator::class)->shoppingList)->toBeEmpty();
});

test('mount initializes empty checked items', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();

    expect(testFixture('page', ShoppingListGenerator::class)->checkedItems)->toBeEmpty();
});

test('toggle item adds item to checked', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();

    testFixture('page', ShoppingListGenerator::class)->toggleItem(0);

    expect(testFixture('page', ShoppingListGenerator::class)->checkedItems)->toHaveKey(0)
        ->and(testFixture('page', ShoppingListGenerator::class)->checkedItems[0])->toBeTrue();
});

test('toggle item removes item from checked', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();
    testFixture('page', ShoppingListGenerator::class)->checkedItems[0] = true;

    testFixture('page', ShoppingListGenerator::class)->toggleItem(0);

    expect(testFixture('page', ShoppingListGenerator::class)->checkedItems)->not->toHaveKey(0);
});

test('generate shopping list resets checked items', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();
    testFixture('page', ShoppingListGenerator::class)->checkedItems = [0 => true, 1 => true];

    testFixture('page', ShoppingListGenerator::class)->generateShoppingList(resolve(OrderIngredientAggregator::class));

    expect(testFixture('page', ShoppingListGenerator::class)->checkedItems)->toBeEmpty();
});

test('generate shopping list populates list', function () {
    testFixture('page', ShoppingListGenerator::class)->mount();
    testFixture('page', ShoppingListGenerator::class)->generateShoppingList(resolve(OrderIngredientAggregator::class));

    expect(testFixture('page', ShoppingListGenerator::class)->shoppingList)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
