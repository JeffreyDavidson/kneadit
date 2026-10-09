<?php

use App\Filament\Pages\Tools\SmartShoppingList;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

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

    expect(test()->page->supplierGroups)->toBeInstanceOf(Collection::class);
});

test('generate list populates supplier groups', function () {
    test()->page->mount();
    test()->page->generateList();

    expect(test()->page->supplierGroups)->toBeInstanceOf(Collection::class);
});

test('toggle upcoming flips flag and regenerates list', function () {
    test()->page->mount();

    expect(test()->page->includeUpcoming)->toBeFalse();

    test()->page->toggleUpcoming();

    expect(test()->page->includeUpcoming)->toBeTrue();

    test()->page->toggleUpcoming();

    expect(test()->page->includeUpcoming)->toBeFalse();
});

test('the planning window starts on the bakery-local date in the evening', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    test()->page->mount();

    expect(test()->page->startDate)->toBe('2026-10-05')
        ->and(test()->page->endDate)->toBe('2026-10-12');
});

test('the list groups an ingredient under its priced supplier, not an unpriced one', function () {
    $ingredient = Ingredient::factory()->lowStock()->create();
    $unpriced = Supplier::factory()->create();
    $priced = Supplier::factory()->create();
    $ingredient->suppliers()->attach($unpriced->id, ['unit_price' => null]);
    $ingredient->suppliers()->attach($priced->id, ['unit_price' => 12.00]);

    test()->page->mount();

    expect(test()->page->supplierGroups->keys()->all())->toBe([$priced->id]);
});
