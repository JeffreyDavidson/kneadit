<?php

use App\Actions\GiftCards\RedeemGiftCard;
use App\Actions\Orders\ModifyOrder;
use App\Actions\Orders\ReverseOrderDiscounts;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Events\Orders\OrderModified;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['order_modification_window_minutes' => 30]);
});

test('updates quantities and recalculates totals', function () {
    Event::fake();

    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create([
        'subtotal' => 20.00,
        'total' => 20.00,
    ]);
    $item = OrderItem::factory()->for($order)->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 10.00,
    ]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 5],
    ]);

    $order->refresh();
    expect($order->orderItems()->first()->quantity)->toBe(5)
        ->and($order->subtotal->dollars())->toBe(50.00)
        ->and($order->total->dollars())->toBe(50.00);
    Event::assertDispatched(OrderModified::class);
});

test('removes item when quantity is set to zero', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create();
    $keep = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);
    $remove = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $keep->id, 'quantity' => 1],
        ['order_item_id' => $remove->id, 'quantity' => 0],
    ]);

    $order->refresh();
    expect($order->orderItems)->toHaveCount(1)
        ->and($order->subtotal->dollars())->toBe(10.00);
});

test('updates tip when provided', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ], tipAmount: 4.50);

    $order->refresh();
    expect($order->tip_amount->dollars())->toBe(4.50)
        ->and($order->total->dollars())->toBe(24.50);
});

test('throws when order is not in pending status', function () {
    $order = Order::factory()->confirmed()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when order is already paid', function () {
    $order = Order::factory()->pending()->paid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when modification window has expired', function () {
    settings(['order_modification_window_minutes' => 5]);

    $order = Order::factory()->pending()->unpaid()->create([
        'created_at' => now()->subMinutes(10),
    ]);
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when feature is disabled (window = 0)', function () {
    settings(['order_modification_window_minutes' => 0]);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('rolls back when modification would leave order with no items', function () {
    $order = Order::factory()->pending()->unpaid()->create();
    $only = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $only->id, 'quantity' => 0],
    ]))->toThrow(OrderNotModifiableException::class)
        ->and($order->fresh()->orderItems()->count())->toBe(1);
});

test('throws InsufficientStockException when modification exceeds ingredient stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 6],
    ]))->toThrow(
        InsufficientStockException::class,
        'insufficient stock for Flour',
    )
        ->and($order->fresh()->orderItems()->first()->quantity)->toBe(2);
});

test('rolls back item-quantity changes when stock check fails', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $sugar = Ingredient::factory()->create(['name' => 'Sugar', 'current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($sugar->id, ['quantity' => 1.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 10.00]);

    try {
        resolve(ModifyOrder::class)($order, [
            ['order_item_id' => $item->id, 'quantity' => 10],
        ]);
    } catch (InsufficientStockException) {
        // expected
    }

    expect($order->fresh()->orderItems()->first()->quantity)->toBe(3);
});

test('reports every shortage when several ingredients fall short', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $butter = Ingredient::factory()->create(['name' => 'Butter', 'current_stock' => 2.00]);
    $eggs = Ingredient::factory()->create(['name' => 'Eggs', 'current_stock' => 4.00]);
    $recipe->inventoryIngredients()->attach($butter->id, ['quantity' => 1.0, 'unit' => 'lb']);
    $recipe->inventoryIngredients()->attach($eggs->id, ['quantity' => 2.0, 'unit' => 'each']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 5],
    ]))->toThrow(InsufficientStockException::class, 'Butter, Eggs');
});

test('allows modification when stock is sufficient', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 100.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 10],
    ]);

    expect($order->fresh()->orderItems()->first()->quantity)->toBe(10);
});

test('allows decreasing quantity even when current order draw exceeds stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 4.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 10.00]);

    // Current order would need 10 lb; only 4 lb on hand. Decreasing to 2 needs
    // 4 lb — fits exactly, so the modification must be allowed.
    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]);

    expect($order->fresh()->orderItems()->first()->quantity)->toBe(2);
});

/**
 * A pending, unpaid order of one $10 line item, with the stored totals a
 * checkout would have produced.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeModifiableOrder(int $quantity, array $attributes = []): Order
{
    $product = Product::factory()->create(['price' => 10.00]);
    $subtotal = $quantity * 10.00;

    $order = Order::factory()->pending()->unpaid()->create([
        'subtotal' => $subtotal,
        'total' => $subtotal,
        ...$attributes,
    ]);

    OrderItem::factory()->for($order)->create([
        'product_id' => $product->id,
        'quantity' => $quantity,
        'unit_price' => 10.00,
    ]);

    return $order;
}

/**
 * @return array<string, mixed>
 */
function makeCouponOrder(int $quantity, float $discount): array
{
    $coupon = Coupon::factory()->create(['used_count' => 1]);
    $order = makeModifiableOrder($quantity, [
        'coupon_id' => $coupon->id,
        'discount_amount' => $discount,
        'total' => ($quantity * 10.00) - $discount,
    ]);
    CouponTransaction::factory()->usage()->create([
        'coupon_id' => $coupon->id,
        'order_id' => $order->id,
        'amount' => $discount,
    ]);

    return ['order' => $order, 'coupon' => $coupon];
}

function couponUsageCents(Order $order): int
{
    return CouponTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', CouponTransactionType::Usage)
        ->firstOrFail()
        ->amount
        ->cents();
}

function modifyQuantity(Order $order, int $quantity): Order
{
    return resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $order->orderItems()->firstOrFail()->id, 'quantity' => $quantity],
    ]);
}

test('scales the discount down with the subtotal and updates the coupon usage', function (float $discount, int $startQuantity, int $newQuantity, float $expectedDiscount, float $expectedTotal) {
    ['order' => $order] = makeCouponOrder($startQuantity, $discount);

    $modified = modifyQuantity($order, $newQuantity);

    expect($modified->discount_amount->dollars())->toBe($expectedDiscount)
        ->and($modified->total->dollars())->toBe($expectedTotal)
        ->and(couponUsageCents($modified))->toBe((int) round($expectedDiscount * 100));
})->with([
    '20% coupon on $100 reduced to $50' => [20.00, 10, 5, 10.00, 40.00],
    'fixed $10 on $100 reduced to $20' => [10.00, 10, 2, 2.00, 18.00],
    'discount never exceeds the order' => [20.00, 10, 1, 2.00, 8.00],
]);

test('does not scale the discount up when the quantity increases', function () {
    ['order' => $order] = makeCouponOrder(10, 20.00);

    $modified = modifyQuantity($order, 15);

    expect($modified->discount_amount->dollars())->toBe(20.00)
        ->and($modified->total->dollars())->toBe(130.00)
        ->and(couponUsageCents($modified))->toBe(2000);
});

test('returns to the original discount when an edit is undone without drift', function () {
    ['order' => $order] = makeCouponOrder(10, 20.00);

    $down = modifyQuantity($order, 3);
    $back = modifyQuantity($down->fresh(), 10);

    expect($down->discount_amount->dollars())->toBe(6.00)
        ->and($back->discount_amount->dollars())->toBe(20.00)
        ->and($back->total->dollars())->toBe(80.00)
        ->and(couponUsageCents($back))->toBe(2000);
});

test('scales the whole discount for orders without a coupon', function () {
    $order = makeModifiableOrder(10, ['discount_amount' => 20.00, 'total' => 80.00]);

    $modified = modifyQuantity($order, 5);

    expect($modified->discount_amount->dollars())->toBe(10.00)
        ->and($modified->total->dollars())->toBe(40.00);
});

test('records the subtotal and discount at placement the first time an order is edited', function () {
    $order = makeModifiableOrder(10, ['discount_amount' => 20.00, 'total' => 80.00]);

    $modified = modifyQuantity($order, 5);

    expect($modified->original_subtotal?->dollars())->toBe(100.00)
        ->and($modified->original_discount_amount?->dollars())->toBe(20.00);
});

test('credits the unused gift card amount back and shrinks the redemption', function () {
    $card = GiftCard::factory()->create(['initial_balance' => 100.00, 'current_balance' => 100.00]);
    $order = makeModifiableOrder(10, [
        'gift_card_id' => $card->id,
        'gift_card_amount' => 50.00,
        'total' => 50.00,
        'tip_amount' => 3.00,
    ]);
    resolve(RedeemGiftCard::class)($card->code, 50.00, $order->id);

    $modified = modifyQuantity($order, 2);

    $redemption = GiftCardTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', GiftCardTransactionType::Redemption)
        ->firstOrFail();

    expect($modified->gift_card_amount->dollars())->toBe(20.00)
        ->and($modified->total->dollars())->toBe(3.00)
        ->and($card->refresh()->current_balance->dollars())->toBe(80.00)
        ->and($redemption->amount->dollars())->toBe(-20.00);
});

test('refunds exactly the reduced gift card amount when the edited order is cancelled', function () {
    $card = GiftCard::factory()->create(['initial_balance' => 100.00, 'current_balance' => 100.00]);
    $order = makeModifiableOrder(10, [
        'gift_card_id' => $card->id,
        'gift_card_amount' => 50.00,
        'total' => 50.00,
    ]);
    resolve(RedeemGiftCard::class)($card->code, 50.00, $order->id);

    $modified = modifyQuantity($order, 2);
    resolve(ReverseOrderDiscounts::class)($modified->fresh(), 'Order cancelled');

    expect($card->refresh()->current_balance->dollars())->toBe(100.00);
});

test('never increases the gift card draw when the order grows', function () {
    $card = GiftCard::factory()->create(['initial_balance' => 100.00, 'current_balance' => 100.00]);
    $order = makeModifiableOrder(10, [
        'gift_card_id' => $card->id,
        'gift_card_amount' => 50.00,
        'total' => 50.00,
    ]);
    resolve(RedeemGiftCard::class)($card->code, 50.00, $order->id);

    $modified = modifyQuantity($order, 15);

    expect($modified->gift_card_amount->dollars())->toBe(50.00)
        ->and($modified->total->dollars())->toBe(100.00)
        ->and($card->refresh()->current_balance->dollars())->toBe(50.00);
});

test('rejects an edit that drops a free delivery order below the free delivery minimum', function () {
    settings(['free_delivery_minimum' => '50']);
    $order = makeModifiableOrder(6)->forceFill(['delivery_type' => 'delivery', 'delivery_fee' => 0]);
    $order->save();

    expect(fn () => modifyQuantity($order, 4))->toThrow(OrderNotModifiableException::class, 'free delivery minimum');

    $order->refresh();
    expect($order->orderItems()->first()->quantity)->toBe(6)
        ->and($order->subtotal->dollars())->toBe(60.00)
        ->and($order->total->dollars())->toBe(60.00);
});

test('allows editing a zero fee delivery order that never qualified for free delivery', function () {
    settings(['free_delivery_minimum' => '50']);
    $order = makeModifiableOrder(3)->forceFill(['delivery_type' => 'delivery', 'delivery_fee' => 0]);
    $order->save();

    $modified = modifyQuantity($order, 2);

    expect($modified->subtotal->dollars())->toBe(20.00);
});

test('rejects an edit that drops an order below its minimum order amount', function (string $deliveryType, string $setting) {
    settings([$setting => '25']);
    $order = makeModifiableOrder(3, ['delivery_type' => $deliveryType, 'delivery_fee' => 5.00, 'total' => 35.00]);

    expect(fn () => modifyQuantity($order, 2))->toThrow(OrderNotModifiableException::class, 'below the $25.00 minimum')
        ->and($order->fresh()->orderItems()->first()->quantity)->toBe(3);
})->with([
    'pickup' => ['pickup', 'minimum_pickup_order_amount'],
    'delivery' => ['delivery', 'minimum_delivery_order_amount'],
]);

test('allows raising an order that is already below the minimum', function () {
    settings(['minimum_pickup_order_amount' => '25']);
    $order = makeModifiableOrder(2);

    $modified = modifyQuantity($order, 3);

    expect($modified->subtotal->dollars())->toBe(30.00);
});
