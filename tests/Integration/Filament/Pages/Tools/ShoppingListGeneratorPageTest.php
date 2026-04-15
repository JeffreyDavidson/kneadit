<?php

use App\Filament\Pages\Tools\ShoppingListGenerator;
use App\Services\Orders\OrderIngredientAggregator;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ShoppingListGenerator;
});

test('mount sets start date to today', function () {
    test()->page->mount();

    expect(test()->page->startDate)->toBe(now()->format('Y-m-d'));
});

test('mount sets end date to planning days ahead', function () {
    test()->page->mount();

    $expectedEnd = now()->addDays(config('orders.default_planning_days', 7))->format('Y-m-d');
    expect(test()->page->endDate)->toBe($expectedEnd);
});

test('mount initializes empty shopping list', function () {
    test()->page->mount();

    expect(test()->page->shoppingList)->toBeEmpty();
});

test('mount initializes empty checked items', function () {
    test()->page->mount();

    expect(test()->page->checkedItems)->toBeEmpty();
});

test('toggle item adds item to checked', function () {
    test()->page->mount();

    test()->page->toggleItem(0);

    expect(test()->page->checkedItems)->toHaveKey(0)
        ->and(test()->page->checkedItems[0])->toBeTrue();
});

test('toggle item removes item from checked', function () {
    test()->page->mount();
    test()->page->checkedItems[0] = true;

    test()->page->toggleItem(0);

    expect(test()->page->checkedItems)->not->toHaveKey(0);
});

test('generate shopping list resets checked items', function () {
    test()->page->mount();
    test()->page->checkedItems = [0 => true, 1 => true];

    test()->page->generateShoppingList(resolve(OrderIngredientAggregator::class));

    expect(test()->page->checkedItems)->toBeEmpty();
});

test('generate shopping list populates list', function () {
    test()->page->mount();
    test()->page->generateShoppingList(resolve(OrderIngredientAggregator::class));

    expect(test()->page->shoppingList)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
