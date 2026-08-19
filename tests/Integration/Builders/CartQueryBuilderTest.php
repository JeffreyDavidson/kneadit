<?php

use App\Builders\Orders\CartQueryBuilder;
use App\Models\Orders\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function cartQuery(): CartQueryBuilder
{
    return Cart::query();
}

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

    $builder = cartQuery();
    $query = (new ReflectionMethod($builder, 'abandonedBefore'))->invoke($builder, now()->subHours(12));
    throw_unless($query instanceof CartQueryBuilder, RuntimeException::class, 'Expected the custom cart builder.');
    $carts = $query->get();

    expect($carts)->toHaveCount(1)
        ->and($carts->firstOrFail()->is($abandoned))->toBeTrue();
});
