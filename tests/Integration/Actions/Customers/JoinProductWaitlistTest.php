<?php

use App\Actions\Customers\JoinProductWaitlist;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it creates a waitlist entry for a product', function () {
    $product = Product::factory()->create();

    $action = new JoinProductWaitlist;
    $entry = $action(
        productId: $product->id,
        customerEmail: 'jane@example.com',
        customerName: 'Jane Baker',
    );

    expect($entry)->toBeInstanceOf(ProductWaitlist::class)
        ->and($entry->product_id)->toBe($product->id)
        ->and($entry->customer_email)->toBe('jane@example.com')
        ->and($entry->customer_name)->toBe('Jane Baker')
        ->and($entry->notified_at)->toBeNull();
});

test('it resets notified_at when re-joining a waitlist', function () {
    $product = Product::factory()->create();

    ProductWaitlist::factory()->create([
        'product_id' => $product->id,
        'customer_email' => 'jane@example.com',
        'notified_at' => now(),
    ]);

    $action = new JoinProductWaitlist;
    $entry = $action(
        productId: $product->id,
        customerEmail: 'jane@example.com',
    );

    expect($entry->notified_at)->toBeNull();
});
