<?php

use App\Services\Export\CsvExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

it('returns valid export types', function () {
    $service = new CsvExportService;

    expect($service->validTypes())->toBe(['products', 'categories', 'orders', 'customers', 'reviews']);
});

it('returns early for invalid type', function () {
    $service = new CsvExportService;
    $handle = fopen('php://temp', 'r+');

    $service->writeTo($handle, 'nonexistent');

    rewind($handle);
    $content = stream_get_contents($handle);
    fclose($handle);

    expect($content)->toBeEmpty();
});

it('writes products CSV with headers and data', function () {
    DB::table('categories')->insert([
        'id' => 1,
        'name' => 'Bread',
        'slug' => 'bread',
        'is_active' => true,
        'sort_order' => 0,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    DB::table('products')->insert([
        'id' => 1,
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'description' => 'A classic loaf',
        'price' => 8.50,
        'category_id' => 1,
        'is_active' => true,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    $service = new CsvExportService;
    $content = $service->toString('products');

    expect($content)
        ->toContain('ID,Name,Slug,Description,Price,Status')
        ->toContain('Sourdough');
});

it('writes orders CSV with join to order_items', function () {
    DB::table('customers')->insert([
        'id' => 1,
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    DB::table('orders')->insert([
        'id' => 1,
        'order_number' => 'ORD-000001',
        'customer_id' => 1,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'subtotal' => 20.00,
        'total' => 20.00,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    DB::table('categories')->insert([
        'id' => 1,
        'name' => 'Bread',
        'slug' => 'bread',
        'is_active' => true,
        'sort_order' => 0,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    DB::table('products')->insert([
        'id' => 5,
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 10.00,
        'category_id' => 1,
        'is_active' => true,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    DB::table('order_items')->insert([
        'id' => 1,
        'order_id' => 1,
        'product_id' => 5,
        'quantity' => 2,
        'unit_price' => 10.00,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    $service = new CsvExportService;
    $content = $service->toString('orders');

    expect($content)
        ->toContain('Status,Total')
        ->toContain('pending');
});

it('writes reviews CSV with fallback columns', function () {
    DB::table('reviews')->insert([
        'id' => 1,
        'product_id' => null,
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'rating' => 5,
        'comment' => 'Great product!',
        'is_approved' => true,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    $service = new CsvExportService;
    $content = $service->toString('reviews');

    expect($content)
        ->toContain('Rating')
        ->toContain('Comment')
        ->toContain('Great product!');
});

it('generates CSV as a string via toString', function () {
    $service = new CsvExportService;
    $content = $service->toString('categories');

    // Should at least have headers
    expect($content)->toContain('ID,Name,Slug,Description');
});

it('writes customers CSV with data from users table', function () {
    DB::table('users')->insert([
        'id' => 1,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    $service = new CsvExportService;
    $content = $service->toString('customers');

    expect($content)
        ->toContain('ID,Name,Email')
        ->toContain('Jane Doe')
        ->toContain('jane@example.com');
});

it('writes categories CSV with data', function () {
    DB::table('categories')->insert([
        'id' => 1,
        'name' => 'Pastries',
        'slug' => 'pastries',
        'description' => 'Fresh pastries',
        'is_active' => true,
        'sort_order' => 0,
        'created_at' => '2026-01-01 00:00:00',
        'updated_at' => '2026-01-01 00:00:00',
    ]);

    $service = new CsvExportService;
    $content = $service->toString('categories');

    expect($content)
        ->toContain('Pastries')
        ->toContain('pastries')
        ->toContain('Fresh pastries');
});
