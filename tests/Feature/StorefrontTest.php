<?php

use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('menu page loads successfully', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
});

test('menu page shows store name from settings', function () {
    settings(['store_name' => 'Sweet Dreams Bakery']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertSee('Sweet Dreams Bakery');
});

test('menu page shows categories and products', function () {
    $category = Category::query()->create([
        'name' => 'Pastries',
        'slug' => 'pastries',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    Product::query()->create([
        'name' => 'Croissant',
        'slug' => 'croissant',
        'price' => 4.50,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertSee('Pastries');
    $response->assertSee('Croissant');
});

test('menu page shows products with prices', function () {
    $category = Category::query()->create([
        'name' => 'Cakes',
        'slug' => 'cakes',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    Product::query()->create([
        'name' => 'Red Velvet Cake',
        'slug' => 'red-velvet-cake',
        'price' => 35.00,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertSee('Red Velvet Cake');
    $response->assertSee('35.00');
});

test('menu hides inactive categories', function () {
    Category::query()->create([
        'name' => 'Seasonal Only',
        'slug' => 'seasonal-only',
        'is_active' => false,
        'sort_order' => 1,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertDontSee('Seasonal Only');
});

test('menu does not show inactive products', function () {
    $category = Category::query()->create([
        'name' => 'Breads',
        'slug' => 'breads',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    Product::query()->create([
        'name' => 'Hidden Bread',
        'slug' => 'hidden-bread',
        'price' => 5.00,
        'category_id' => $category->id,
        'is_active' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.menu', [], false));

    $response->assertOk();
    $response->assertDontSee('Hidden Bread');
});

test('about page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false));

    $response->assertOk();
});

test('contact page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('contact.show', [], false));

    $response->assertOk();
});

test('contact form submission works with valid data', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about orders',
            'message' => 'Do you offer gluten-free options?',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Question about orders',
    ]);
});

test('contact form validation rejects missing name', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

    $response->assertSessionHasErrors('name');
});

test('contact form validation rejects missing email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

    $response->assertSessionHasErrors('email');
});

test('contact form validation rejects missing subject', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'message' => 'Test message',
        ]);

    $response->assertSessionHasErrors('subject');
});

test('contact form validation rejects missing message', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Test',
        ]);

    $response->assertSessionHasErrors('message');
});
