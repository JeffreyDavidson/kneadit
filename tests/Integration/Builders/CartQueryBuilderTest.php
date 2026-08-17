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
        ->and($results->firstOrFail()->id)->toBe($target->id);
});

test('notConverted excludes carts that have been converted', function () {
    $open = Cart::factory()->create(['converted_at' => null]);
    Cart::factory()->create(['converted_at' => now()]);

    $results = Cart::query()->notConverted()->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($open->id);
});
