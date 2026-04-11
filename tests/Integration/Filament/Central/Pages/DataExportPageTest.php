<?php

use App\Filament\Central\Pages\DataExport;
use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new DataExport;
});

test('selected tenant defaults to null', function () {
    expect(test()->page->selectedTenant)->toBeNull();
});

test('counts defaults to empty array', function () {
    expect(test()->page->counts)->toBeEmpty();
});

test('get tenants returns empty when no tenants', function () {
    expect(test()->page->getTenants())->toBeEmpty();
});

test('get tenants returns tenants ordered by store name', function () {
    Tenant::factory()->create(['store_name' => 'Zebra Bakery']);
    Tenant::factory()->create(['store_name' => 'Alpha Bakery']);

    $tenants = test()->page->getTenants();

    expect($tenants)->toHaveCount(2)
        ->and(array_values($tenants)[0])->toBe('Alpha Bakery');
});

test('get tenants uses name when store name is empty', function () {
    Tenant::factory()->create(['store_name' => null, 'name' => 'Fallback Name']);

    $tenants = test()->page->getTenants();

    expect(array_values($tenants)[0])->toBe('Fallback Name');
});

test('updated selected tenant clears counts when null', function () {
    test()->page->counts = ['products' => 5];
    test()->page->selectedTenant = null;

    test()->page->updatedSelectedTenant(null);

    expect(test()->page->counts)->toBeEmpty();
});

test('updated selected tenant clears counts when tenant not found', function () {
    test()->page->selectedTenant = 'nonexistent-id';

    test()->page->updatedSelectedTenant('nonexistent-id');

    expect(test()->page->counts)->toBeEmpty();
});

test('get export types returns expected types', function () {
    $types = test()->page->getExportTypes();

    expect($types)->toHaveKeys(['products', 'categories', 'orders', 'customers', 'reviews'])
        ->and($types['products'])->toHaveKeys(['icon', 'name', 'description'])
        ->and($types['products']['name'])->toBe('Products');
});

test('get export types icons are heroicons', function () {
    $types = test()->page->getExportTypes();

    foreach ($types as $type) {
        expect($type['icon'])->toBeInstanceOf(Heroicon::class);
    }
});
