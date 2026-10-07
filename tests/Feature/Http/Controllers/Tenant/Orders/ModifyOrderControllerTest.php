<?php

use App\Mail\Orders\OrderModifiedMail;
use App\Models\Customers\Customer;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
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
        ->for(test()->order)
        ->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);
});

test('modify endpoint updates order and queues confirmation email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->post(route('order.modify', test()->order), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 4],
            ],
            'tip_amount' => 2.50,
        ]);

    $response->assertRedirect(route('order.confirmation', test()->order));

    test()->order->refresh();
    expect(test()->order->orderItems()->first()->quantity)->toBe(4)
        ->and(test()->order->tip_amount->dollars())->toBe(2.50);

    Mail::assertQueued(OrderModifiedMail::class);
});

test('modify endpoint returns 422 on invalid payload', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->post(route('order.modify', test()->order), [
            'items' => [],
        ]);

    $response->assertSessionHasErrors(['items']);
});

test('modify endpoint returns session error when window has expired', function () {
    settings(['order_modification_window_minutes' => 1]);
    test()->order->forceFill(['created_at' => now()->subMinutes(5)])->save();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->post(route('order.modify', test()->order), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 4],
            ],
        ]);

    $response->assertSessionHasErrors(['items']);
    expect(test()->order->fresh()->orderItems()->first()->quantity)->toBe(2);
});

test('modify endpoint answers a stock shortage with a friendly error instead of a 500', function () {
    $recipe = Recipe::factory()->for(test()->item->product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'kg']);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->from(route('order.confirmation', test()->order))
        ->post(route('order.modify', test()->order), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 6],
            ],
        ]);

    $response->assertRedirect(route('order.confirmation', test()->order))
        ->assertSessionHasErrors(['items' => 'Sorry, we don\'t have enough Flour in stock right now. Please reduce the quantity or remove an item.']);
    expect(test()->order->fresh()->orderItems()->first()->quantity)->toBe(2);
});

test('order confirmation shows the modify form open with the error after a failed edit', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->from(route('order.confirmation', test()->order))
        ->followingRedirects()
        ->post(route('order.modify', test()->order), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 99],
            ],
        ]);

    $response->assertOk()->assertSeeHtml('open: true')->assertSeeHtml('data-test="modify-order-errors"')
        ->assertSee('must not be greater than 20');
});

test('order confirmation keeps the modify form closed when nothing failed', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->get(route('order.confirmation', test()->order));

    $response->assertOk()->assertSeeHtml('open: false')->assertDontSeeHtml('data-test="modify-order-errors"');
});

test('modify endpoint returns session error when the edit drops the order below the minimum', function () {
    settings(['minimum_pickup_order_amount' => '15']);

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([test()->order]))
        ->post(route('order.modify', test()->order), [
            'items' => [
                ['order_item_id' => test()->item->id, 'quantity' => 1],
            ],
        ]);

    $response->assertSessionHasErrors(['items']);
    expect(test()->order->fresh()->orderItems()->first()->quantity)->toBe(2);
});
