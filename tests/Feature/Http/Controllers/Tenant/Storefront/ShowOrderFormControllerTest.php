<?php

use App\Models\Inventory\Product;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order controller index passes settings to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('settings', fn (TenantSettings $s) => is_int($s->orders->leadTimeHours) && is_bool($s->orders->deliveryEnabled));
});

test('order controller passes page content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('content')
        ->assertViewHas('storefrontTheme');
});

test('biscotto order page uses the themed presentation without replacing the order form', function () {
    Setting::factory()->create(['key' => 'storefront_theme', 'value' => 'biscotto']);
    resolve(SettingsManager::class)->flushCache();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()->assertSeeHtml('biscotto-order-hero')->assertSeeHtml('biscotto-order-stage')->assertSeeHtml('data-test="order-form"')->assertSeeHtml('data-test="order-form-submit"');
});

test('order page lists the payment methods the baker accepts', function () {
    settings(['payment_methods' => json_encode(['cash', 'paypal'])]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSee('Cash, Paypal');
});

test('order page shows and enforces the earliest delivery date after the order cutoff', function () {
    settings(['minimum_order_lead_hours' => '48', 'timezone' => 'America/New_York']);
    BusinessSchedule::factory()->open()->create(['day_of_week' => 1, 'order_cutoff_time' => '14:00']);
    Date::setTestNow('2026-10-05 19:30');

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSee('ready Thursday, October 8 or later')
        ->assertSeeHtml("minDate: '2026-10-08'");
});

test('the coupon apply button closes its opening tag before its label', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    expect($response->getContent())->toMatch('/data-test="order-form-coupon-apply"[^>]*>\s*<span x-text="isApplyingCoupon/');
});

test('a saved cart is hydrated with current prices and tells the customer which items were removed', function () {
    $product = Product::factory()->create(['name' => 'Sourdough', 'price' => 12.00]);
    $retired = Product::factory()->create(['name' => 'Retired Rye', 'is_active' => false]);
    $cart = Cart::factory()->create();
    CartItem::factory()->recycle($cart, $product)->create(['quantity' => 2, 'unit_price' => 8.00]);
    CartItem::factory()->recycle($cart, $retired)->create(['quantity' => 1, 'unit_price' => 6.00]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withUnencryptedCookie('cart_token', $cart->cart_token)
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('hydratedCartItems', [
            ['id' => $product->id, 'name' => 'Sourdough', 'price' => 12.0, 'quantity' => 2],
        ])
        ->assertViewHas('removedItemNames', ['Retired Rye'])
        ->assertSeeHtml('data-test="order-form-removed-items"')
        ->assertSee('Retired Rye');
});

test('the order form shows no removed items notice when every cart item is still available', function () {
    $product = Product::factory()->create(['price' => 12.00]);
    $cart = Cart::factory()->create();
    CartItem::factory()->recycle($cart, $product)->create(['quantity' => 1]);

    $response = withoutMiddleware(tenantMiddleware())
        ->withUnencryptedCookie('cart_token', $cart->cart_token)
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('removedItemNames', [])
        ->assertSeeHtml('removedItems: []');
});

test('a reorder link tells the customer which items from the earlier order were removed', function () {
    $retired = Product::factory()->create(['name' => 'Retired Rye', 'is_active' => false]);
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order, $retired)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('order.create', ['reorder' => $order->order_number], false));

    $response->assertOk()
        ->assertViewHas('removedItemNames', ['Retired Rye'])
        ->assertSeeHtml('data-test="order-form-removed-items"');
});

test('a reorder link for an order the visitor cannot access shows no removed items notice', function () {
    $retired = Product::factory()->create(['name' => 'Retired Rye', 'is_active' => false]);
    $order = Order::factory()->create();
    OrderItem::factory()->recycle($order, $retired)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', ['reorder' => $order->order_number], false));

    $response->assertOk()
        ->assertViewHas('removedItemNames', [])
        ->assertDontSee('Retired Rye');
});

test('the order form script follows the redirect url the server returns instead of the fetch response url', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()->assertSeeHtml('payload.data.redirect_url')->assertDontSeeHtml('window.location.href = response.url');
});

test('the order form receives the per-item limit and its message instead of hardcoding them', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertViewHas('maxQuantity', OrderItem::MAX_QUANTITY)
        ->assertViewHas('quantityLimitMessage', 'You can order up to 100 of one item. Contact us for larger orders.')
        ->assertSeeHtml('maxQuantity: '.OrderItem::MAX_QUANTITY)
        ->assertSee('You can order up to 100 of one item. Contact us for larger orders.');
});

test('the plus button stops at the limit and explains why', function () {
    Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSeeHtml('data-test="order-form-product-increment"')
        ->assertSeeHtml(':disabled="atMaxQuantity(')
        ->assertSeeHtml('data-test="order-form-quantity-limit"');
});

test('the order form script re-checks the coupon whenever the cart changes and does not stack it with the sale', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()
        ->assertSeeHtml('scheduleCouponRevalidation()')
        ->assertSeeHtml('revalidateCoupon()')
        ->assertSee('Your sale price already beats this coupon')
        ->assertSeeHtml('data-test="order-form-coupon-beaten"');
});

test('the order form script shows the items the reorder endpoint says were removed', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.create', [], false));

    $response->assertOk()->assertSeeHtml('payload.data.removed_items');
});
