<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('menu controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('heroEyebrow')
        ->assertViewHas('ctaDesc')
        ->assertViewHas('storefrontTheme');
});

test('menu page loads successfully', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
});

test('biscotto theme renders the parchment menu presentation', function () {
    settings(['storefront_theme' => 'biscotto']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk()
        ->assertSee('biscotto-menu-hero', false)
        ->assertSee('biscotto-parchment', false)
        ->assertSee('biscotto-menu-order', false);
});

test('menu page shows store name from settings', function () {
    settings(['store_name' => 'Sweet Dreams Bakery']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertSee('Sweet Dreams Bakery');
});

test('menu page shows categories and products', function () {
    $category = Category::factory()->create([
        'name' => 'Pastries',
        'slug' => 'pastries',
    ]);

    Product::factory()->for($category)->create([
        'name' => 'Croissant',
        'slug' => 'croissant',
        'price' => 4.50,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertSee('Pastries');
    $response->assertSee('Croissant');
});

test('menu page shows products with prices', function () {
    $category = Category::factory()->create([
        'name' => 'Cakes',
        'slug' => 'cakes',
    ]);

    Product::factory()->for($category)->create([
        'name' => 'Red Velvet Cake',
        'slug' => 'red-velvet-cake',
        'price' => 35.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertSee('Red Velvet Cake');
    $response->assertSee('35.00');
});

test('menu hides inactive categories', function () {
    Category::factory()->inactive()->create([
        'name' => 'Seasonal Only',
        'slug' => 'seasonal-only',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertDontSee('Seasonal Only');
});

test('menu does not show inactive products', function () {
    $category = Category::factory()->create([
        'name' => 'Breads',
        'slug' => 'breads',
    ]);

    Product::factory()->for($category)->inactive()->create([
        'name' => 'Hidden Bread',
        'slug' => 'hidden-bread',
        'price' => 5.00,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertDontSee('Hidden Bread');
});
