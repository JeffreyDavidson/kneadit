<?php

use App\Actions\Products\NotifyProductWaitlist;
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

test('queues an email per unnotified waitlist entry and marks them notified', function () {
    $product = Product::factory()->create();
    ProductWaitlist::query()->create(['product_id' => $product->id, 'customer_email' => 'a@example.com', 'customer_name' => 'Alice']);
    ProductWaitlist::query()->create(['product_id' => $product->id, 'customer_email' => 'b@example.com', 'customer_name' => 'Bob']);

    $count = resolve(NotifyProductWaitlist::class)($product);

    expect($count)->toBe(2);
    Mail::assertQueued(ProductAvailableMail::class, fn (ProductAvailableMail $mail) => $mail->hasTo('a@example.com'));
    Mail::assertQueued(ProductAvailableMail::class, fn (ProductAvailableMail $mail) => $mail->hasTo('b@example.com'));
    expect(ProductWaitlist::query()->whereNull('notified_at')->count())->toBe(0);
});

test('skips entries already notified', function () {
    $product = Product::factory()->create();
    ProductWaitlist::query()->create(['product_id' => $product->id, 'customer_email' => 'fresh@example.com']);
    ProductWaitlist::query()->create(['product_id' => $product->id, 'customer_email' => 'already@example.com', 'notified_at' => now()->subDay()]);

    $count = resolve(NotifyProductWaitlist::class)($product);

    expect($count)->toBe(1);
    Mail::assertQueued(ProductAvailableMail::class, fn (ProductAvailableMail $mail) => $mail->hasTo('fresh@example.com'));
    Mail::assertNotQueued(ProductAvailableMail::class, fn (ProductAvailableMail $mail) => $mail->hasTo('already@example.com'));
});

test('does nothing and returns 0 when the email toggle is disabled', function () {
    settings(['email_product_available_enabled' => false]);
    $product = Product::factory()->create();
    ProductWaitlist::query()->create(['product_id' => $product->id, 'customer_email' => 'a@example.com']);

    $count = resolve(NotifyProductWaitlist::class)($product);

    expect($count)->toBe(0);
    Mail::assertNothingQueued();
    expect(ProductWaitlist::query()->whereNotNull('notified_at')->count())->toBe(0);
});

test('returns 0 when waitlist is empty', function () {
    $product = Product::factory()->create();

    expect(resolve(NotifyProductWaitlist::class)($product))->toBe(0);
    Mail::assertNothingQueued();
});
