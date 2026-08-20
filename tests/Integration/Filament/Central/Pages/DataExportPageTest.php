<?php

use App\Filament\Central\Pages\DataExport;
use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new DataExport;
});

test('selected tenant defaults to null', function () {
    expect(testFixture('page', DataExport::class)->selectedTenant)->toBeNull();
});

test('counts defaults to empty array', function () {
    expect(testFixture('page', DataExport::class)->counts)->toBeEmpty();
});

test('get tenants returns empty when no tenants', function () {
    expect(testFixture('page', DataExport::class)->getTenants())->toBeEmpty();
});

test('get tenants returns tenants ordered by store name', function () {
    Tenant::factory()->create(['store_name' => 'Zebra Bakery']);
    Tenant::factory()->create(['store_name' => 'Alpha Bakery']);

    $tenants = testFixture('page', DataExport::class)->getTenants();

    expect($tenants)->toHaveCount(2)
        ->and(array_values($tenants)[0])->toBe('Alpha Bakery');
});

test('get tenants uses name when store name is empty', function () {
    Tenant::factory()->create(['store_name' => null, 'name' => 'Fallback Name']);

    $tenants = testFixture('page', DataExport::class)->getTenants();

    expect(array_values($tenants)[0])->toBe('Fallback Name');
});

test('updated selected tenant clears counts when null', function () {
    testFixture('page', DataExport::class)->counts = ['products' => 5];
    testFixture('page', DataExport::class)->selectedTenant = null;

    testFixture('page', DataExport::class)->updatedSelectedTenant(null);

    expect(testFixture('page', DataExport::class)->counts)->toBeEmpty();
});

test('updated selected tenant clears counts when tenant not found', function () {
    testFixture('page', DataExport::class)->selectedTenant = 'nonexistent-id';

    testFixture('page', DataExport::class)->updatedSelectedTenant('nonexistent-id');

    expect(testFixture('page', DataExport::class)->counts)->toBeEmpty();
});

test('get export types returns expected types', function () {
    $types = testFixture('page', DataExport::class)->getExportTypes();
    $products = $types['products'] ?? null;
    throw_unless(is_array($products), RuntimeException::class, 'Expected product export type.');

    expect($types)->toHaveKeys(['products', 'categories', 'orders', 'customers', 'reviews'])
        ->and($products)->toHaveKeys(['icon', 'name', 'description'])
        ->and($products['name'] ?? null)->toBe('Products');
});

test('get export types icons are heroicons', function () {
    $types = testFixture('page', DataExport::class)->getExportTypes();

    foreach ($types as $type) {
        throw_unless(is_array($type), RuntimeException::class, 'Expected an export type array.');
        expect($type['icon'] ?? null)->toBeInstanceOf(Heroicon::class);
    }
});
