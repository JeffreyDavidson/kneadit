<?php

use App\Enums\Financial\CouponType;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Models\Financial\Coupon;
use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders items + recovery link when no coupon', function () {
    $product = Product::factory()->create(['name' => 'Croissant']);
    $cart = Cart::factory()->withEmail('alice@example.com')->create(['customer_name' => 'Alice']);
    CartItem::factory()->for($cart)->create(['product_id' => $product->id, 'quantity' => 3]);

    $cart->load('items.product');
    $rendered = (new AbandonedCartRecoveryMail($cart, null))->render();

    expect($rendered)
        ->toContain('Alice')
        ->toContain('Croissant')
        ->toContain('3 ×')
        ->toContain('Finish my order');
});

test('renders coupon card when a coupon is attached', function () {
    $product = Product::factory()->create(['name' => 'Croissant']);
    $cart = Cart::factory()->withEmail('alice@example.com')->create();
    CartItem::factory()->for($cart)->create(['product_id' => $product->id, 'quantity' => 1]);

    $coupon = Coupon::factory()->create([
        'code' => 'BACK-ABCDE',
        'type' => CouponType::Fixed,
        'fixed_amount' => Money::fromDollars(5.0),
        'max_uses' => 1,
        'is_active' => true,
    ]);

    $cart->load('items.product');
    $rendered = (new AbandonedCartRecoveryMail($cart, $coupon))->render();

    expect($rendered)
        ->toContain('BACK-ABCDE')
        ->toContain('Your welcome-back coupon');
});

test('subject mentions the cart', function () {
    $cart = Cart::factory()->create();

    expect((new AbandonedCartRecoveryMail($cart))->envelope()->subject)
        ->toBe('You left something in your cart');
});
