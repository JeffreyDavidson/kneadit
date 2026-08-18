<?php

use App\Events\Customers\ProductWaitlistJoined;
use App\Listeners\Customers\NotifyBakerOfWaitlistJoinListener;
use App\Mail\Customers\NewWaitlistJoinNotificationMail;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('queues notification to the configured store email', function () {
    settings(['store_email' => 'baker@example.com']);
    $product = Product::factory()->create(['name' => 'Croissant']);
    $entry = ProductWaitlist::query()->create([
        'product_id' => $product->id,
        'customer_email' => 'alice@example.com',
        'customer_name' => 'Alice',
    ]);

    (new NotifyBakerOfWaitlistJoinListener)->handle(new ProductWaitlistJoined($entry));

    Mail::assertQueued(
        NewWaitlistJoinNotificationMail::class,
        fn (NewWaitlistJoinNotificationMail $mail): bool => $mail->hasTo('baker@example.com')
            && $mail->entry->is($entry),
    );
});

test('skips when no store email is configured', function () {
    settings(['store_email' => '']);
    $product = Product::factory()->create();
    $entry = ProductWaitlist::query()->create([
        'product_id' => $product->id,
        'customer_email' => 'alice@example.com',
    ]);

    (new NotifyBakerOfWaitlistJoinListener)->handle(new ProductWaitlistJoined($entry));

    Mail::assertNothingQueued();
});
