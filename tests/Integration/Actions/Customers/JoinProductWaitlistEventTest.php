<?php

use App\Actions\Customers\JoinProductWaitlist;
use App\Events\Customers\ProductWaitlistJoined;
use App\Mail\Customers\NewWaitlistJoinNotificationMail;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('fires ProductWaitlistJoined on first join', function () {
    Event::fake();
    $product = Product::factory()->create();

    $entry = resolve(JoinProductWaitlist::class)(
        productId: $product->id,
        customerEmail: 'alice@example.com',
        customerName: 'Alice',
    );

    Event::assertDispatched(fn (ProductWaitlistJoined $e): bool => $e->entry->is($entry));
});

test('does NOT fire on rejoin (same email + product)', function () {
    $product = Product::factory()->create();
    // First call creates the row.
    resolve(JoinProductWaitlist::class)(productId: $product->id, customerEmail: 'alice@example.com');

    Event::fake();
    // Second call updates the existing row — should NOT fire.
    resolve(JoinProductWaitlist::class)(productId: $product->id, customerEmail: 'alice@example.com');

    Event::assertNotDispatched(ProductWaitlistJoined::class);
});

test('fires for a different customer joining the same product', function () {
    $product = Product::factory()->create();
    resolve(JoinProductWaitlist::class)(productId: $product->id, customerEmail: 'alice@example.com');

    Event::fake();
    resolve(JoinProductWaitlist::class)(productId: $product->id, customerEmail: 'bob@example.com');

    Event::assertDispatched(ProductWaitlistJoined::class);
});

test('end-to-end: joining the waitlist queues the baker notification email', function () {
    settings(['store_email' => 'baker@example.com']);
    Mail::fake();
    $product = Product::factory()->create();

    resolve(JoinProductWaitlist::class)(
        productId: $product->id,
        customerEmail: 'alice@example.com',
        customerName: 'Alice',
    );

    Mail::assertQueued(
        NewWaitlistJoinNotificationMail::class,
        fn (NewWaitlistJoinNotificationMail $mail): bool => $mail->hasTo('baker@example.com'),
    );
});
