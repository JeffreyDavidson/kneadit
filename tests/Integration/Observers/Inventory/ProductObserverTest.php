<?php

use App\Mail\Customers\ProductAvailableMail;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('reactivating a product notifies its waitlist', function () {
    $product = Product::factory()->inactive()->create();
    ProductWaitlist::factory()->for($product)->count(2)->create(['notified_at' => null]);

    $product->update(['is_active' => true]);

    Mail::assertQueued(ProductAvailableMail::class, 2);
    expect(ProductWaitlist::query()->whereNull('notified_at')->count())->toBe(0);
});

test('saving an active product again sends nothing', function () {
    $product = Product::factory()->create();
    ProductWaitlist::factory()->for($product)->create(['notified_at' => null]);

    $product->update(['name' => 'Renamed loaf']);

    Mail::assertNothingQueued();
    expect(ProductWaitlist::query()->whereNull('notified_at')->count())->toBe(1);
});

test('deactivating a product sends nothing', function () {
    $product = Product::factory()->create();
    ProductWaitlist::factory()->for($product)->create(['notified_at' => null]);

    $product->update(['is_active' => false]);

    Mail::assertNothingQueued();
});

test('reactivation does not re-send to customers already notified', function () {
    $product = Product::factory()->inactive()->create();
    ProductWaitlist::factory()->for($product)->create(['notified_at' => now()->subDay()]);

    $product->update(['is_active' => true]);

    Mail::assertNothingQueued();
});

test('reactivation sends nothing and keeps entries waiting when the email toggle is off', function () {
    settings(['email_product_available_enabled' => false]);
    $product = Product::factory()->inactive()->create();
    ProductWaitlist::factory()->for($product)->create(['notified_at' => null]);

    $product->update(['is_active' => true]);

    Mail::assertNothingQueued();
    expect(ProductWaitlist::query()->whereNull('notified_at')->count())->toBe(1);
});
