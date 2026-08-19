<?php

use App\Models\Orders\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forToken filters carts by cart_token', function () {
    $target = Cart::factory()->create(['cart_token' => 'token-a']);
    Cart::factory()->create(['cart_token' => 'token-b']);

    $results = Cart::query()->forToken('token-a')->get();

    expect($results)->toHaveCount(1)
        ->and($results->sole()->id)->toBe($target->id);
});

test('notConverted excludes carts that have been converted', function () {
    $open = Cart::factory()->create(['converted_at' => null]);
    Cart::factory()->create(['converted_at' => now()]);

    $results = Cart::query()->notConverted()->get();

    expect($results)->toHaveCount(1)
        ->and($results->sole()->id)->toBe($open->id);
});

test('abandonedBefore returns only recoverable carts older than the cutoff', function () {
    $abandoned = Cart::factory()->withEmail()->abandoned(24)->create();
    Cart::factory()->withEmail()->abandoned(1)->create();
    Cart::factory()->abandoned(24)->create();
    Cart::factory()->withEmail()->abandoned(24)->converted()->create();
    Cart::factory()->withEmail()->abandoned(24)->create(['recovery_sent_at' => now()]);

    $carts = Cart::query()->abandonedBefore(now()->subHours(12))->get();

    expect($carts)->toHaveCount(1)
        ->and($carts->first()?->is($abandoned))->toBeTrue();
});
