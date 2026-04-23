<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Orders\Cart;
use App\Pipes\Orders\MarkCartConverted;
use App\Pipes\Orders\OrderPipelineData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function makeConvertPayload(): OrderPipelineData
{
    return new OrderPipelineData(new CreateOrderData(
        customerName: 'x',
        customerEmail: 'x@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => 1, 'quantity' => 1]],
    ));
}

test('marks the cart matching the cookie as converted', function () {
    $cart = Cart::factory()->create();
    request()->cookies->set('cart_token', $cart->cart_token);

    resolve(MarkCartConverted::class)->handle(makeConvertPayload(), fn ($p) => $p);

    expect($cart->fresh()->converted_at)->not->toBeNull();
});

test('does nothing when no cookie is present', function () {
    $cart = Cart::factory()->create();

    resolve(MarkCartConverted::class)->handle(makeConvertPayload(), fn ($p) => $p);

    expect($cart->fresh()->converted_at)->toBeNull();
});

test('does nothing when no cart matches the cookie', function () {
    request()->cookies->set('cart_token', 'NOT_A_REAL_TOKEN');

    // just asserting no exception is thrown
    resolve(MarkCartConverted::class)->handle(makeConvertPayload(), fn ($p) => $p);

    expect(Cart::query()->whereNotNull('converted_at')->count())->toBe(0);
});
