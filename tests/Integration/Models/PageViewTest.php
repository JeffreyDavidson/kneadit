<?php

use App\Models\Engagement\PageView;
use App\Models\Inventory\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('product relationship returns the associated product', function () {
    $product = Product::factory()->create();
    $pageView = PageView::factory()->create([
        'product_id' => $product->id,
    ]);

    expect($pageView->product->id)->toBe($product->id);
});

test('created at is cast to datetime', function () {
    $pageView = PageView::factory()->create([
        'created_at' => '2026-03-15 10:30:00',
    ]);

    $pageView->refresh();

    expect($pageView->created_at)->toBeInstanceOf(Carbon::class);
});

test('timestamps are disabled', function () {
    $pageView = new PageView;

    expect($pageView->timestamps)->toBeFalse();
});

test('product relationship returns null when no product', function () {
    $pageView = PageView::factory()->create([
        'product_id' => null,
    ]);

    expect($pageView->product)->toBeNull();
});
