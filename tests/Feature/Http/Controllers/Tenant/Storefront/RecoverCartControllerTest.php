<?php

use App\Models\Orders\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('valid signed link redirects to order form with success flash', function () {
    $cart = Cart::factory()->create();

    $url = URL::temporarySignedRoute('cart.recover', now()->addDay(), ['cart_token' => $cart->cart_token]);

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertRedirect(route('order.create'));
    $response->assertSessionHas('success');
});

test('missing cart returns with error flash', function () {
    $url = URL::temporarySignedRoute('cart.recover', now()->addDay(), ['cart_token' => 'NOPE']);

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertRedirect(route('order.create'));
    $response->assertSessionHasErrors(['cart']);
});

test('converted cart returns with error flash', function () {
    $cart = Cart::factory()->converted()->create();

    $url = URL::temporarySignedRoute('cart.recover', now()->addDay(), ['cart_token' => $cart->cart_token]);

    $response = withoutMiddleware(tenantMiddleware())->get($url);

    $response->assertRedirect(route('order.create'));
    $response->assertSessionHasErrors(['cart']);
});

test('unsigned request is rejected', function () {
    $cart = Cart::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())->get("/cart/recover/{$cart->cart_token}");

    $response->assertForbidden();
});
