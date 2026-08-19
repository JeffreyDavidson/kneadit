<?php

use App\Mail\Orders\OrderModifiedMail;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    settings(['order_modification_window_minutes' => 30]);

    $user = User::factory()->owner()->create();
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['price' => 10.00]);

    test()->order = Order::factory()
        ->for($customer)
        ->recycle($user)
        ->pending()
        ->unpaid()
        ->create(['order_number' => 'ORD-MOD-001']);

    test()->item = OrderItem::factory()
        ->for(testFixture('order', Order::class))
        ->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);
});

test('modify endpoint updates order and queues confirmation email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->post(route('order.modify', testFixture('order', Order::class)), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 4],
            ],
            'tip_amount' => 2.50,
        ]);

    $response->assertRedirect(route('order.confirmation', testFixture('order', Order::class)));

    testFixture('order', Order::class)->refresh();
    expect(testFixture('order', Order::class)->orderItems()->first()->quantity)->toBe(4)
        ->and(testFixture('order', Order::class)->tip_amount->dollars())->toBe(2.50);

    Mail::assertQueued(OrderModifiedMail::class);
});

test('modify endpoint returns 422 on invalid payload', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->post(route('order.modify', testFixture('order', Order::class)), [
            'items' => [],
        ]);

    $response->assertSessionHasErrors(['items']);
});

test('modify endpoint returns session error when window has expired', function () {
    settings(['order_modification_window_minutes' => 1]);
    testFixture('order', Order::class)->forceFill(['created_at' => now()->subMinutes(5)])->save();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([testFixture('order', Order::class)]))
        ->post(route('order.modify', testFixture('order', Order::class)), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 4],
            ],
        ]);

    $response->assertSessionHasErrors(['items']);
    expect(testFixture('order', Order::class)->fresh()->orderItems()->first()->quantity)->toBe(2);
});
