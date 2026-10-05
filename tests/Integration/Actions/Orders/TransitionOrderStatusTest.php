<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Inventory\StockAdjustmentType;
use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderStatusChanged;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Services\Inventory\InventoryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use JMac\Testing\Double;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    test()->user = User::factory()->owner()->create();
});

test('transitions order from pending to confirmed', function () {
    $order = Order::factory()->pending()->create();

    $result = resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    expect($result->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('throws exception for invalid transition', function () {
    $order = Order::factory()->pending()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Ready);
})->throws(InvalidOrderTransitionException::class, 'Cannot change status from pending to ready');

test('rejects transitions from terminal delivered state', function () {
    $order = Order::factory()->delivered()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);
})->throws(InvalidOrderTransitionException::class);

test('sends status email on transition', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->create(['status' => OrderStatus::Pending]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Confirmed);
});

test('allowedTransitions returns valid next statuses', function () {
    $pending = Order::factory()->pending()->create();
    $confirmed = Order::factory()->confirmed()->create();
    $delivered = Order::factory()->delivered()->create();

    expect(TransitionOrderStatus::allowedTransitions($pending))
        ->toContain(OrderStatus::Confirmed)
        ->toContain(OrderStatus::Cancelled)
        ->and(TransitionOrderStatus::allowedTransitions($confirmed))
        ->toContain(OrderStatus::Baking)
        ->and(TransitionOrderStatus::allowedTransitions($delivered))
        ->toBeEmpty();
});

test('dispatches OrderStatusChanged after every valid transition', function () {
    Event::fake();
    $order = Order::factory()->pending()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Event::assertDispatched(fn (OrderStatusChanged $event) => $event->from === OrderStatus::Pending
        && $event->to === OrderStatus::Confirmed
        && $event->order->is($order));
});

test('dispatches OrderDelivered on Ready to Delivered transition', function () {
    Event::fake();
    $order = Order::factory()->ready()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Delivered);

    Event::assertDispatched(fn (OrderDelivered $event) => $event->order->is($order));
    Event::assertDispatched(OrderStatusChanged::class);
});

test('dispatches OrderCancelled when cancelling', function () {
    Event::fake();
    $order = Order::factory()->confirmed()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    Event::assertDispatched(fn (OrderCancelled $event) => $event->order->is($order));
});

test('does not dispatch OrderDelivered or OrderCancelled on other transitions', function () {
    Event::fake();
    $order = Order::factory()->pending()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Event::assertNotDispatched(OrderDelivered::class);
    Event::assertNotDispatched(OrderCancelled::class);
});

test('critical effect failure rolls back the status transition', function () {
    $inventoryManager = Double::for(InventoryManager::class);
    $inventoryManager->expects('deductForOrder')
        ->throws(new RuntimeException('Inventory deduction failed'));

    app()->instance(InventoryManager::class, $inventoryManager);

    $order = Order::factory()->confirmed()->create();

    expect(fn () => resolve(TransitionOrderStatus::class)($order, OrderStatus::Baking))
        ->toThrow(RuntimeException::class, 'Inventory deduction failed')
        ->and($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('cancellation decrements coupon used_count and creates reversal transaction', function () {
    $coupon = Coupon::factory()->create(['used_count' => 3]);
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->recycle(test()->user)
        ->confirmed()
        ->create([
            'coupon_id' => $coupon->id,
            'discount_amount' => 5.00,
        ]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    expect($coupon->fresh()->used_count)->toBe(2);

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(CouponTransactionType::Reversal)
        ->and($transaction->coupon_id)->toBe($coupon->id);
});

test('cancellation from Baking restocks ingredients with positive Restock adjustments', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'kg']);

    $order = Order::factory()->confirmed()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 3]);
    $transition = resolve(TransitionOrderStatus::class);
    $transition($order, OrderStatus::Baking);

    $transition($order, OrderStatus::Cancelled);

    expect($flour->fresh()->current_stock)->toBe('10.0000');

    $restock = $flour->stockAdjustments()->where('type', StockAdjustmentType::Restock)->first();
    expect($restock)->not->toBeNull()
        ->and((float) $restock->quantity)->toBe(6.0)
        ->and($restock->notes)->toBe("Order #{$order->order_number} cancelled");
});

test('cancelling restocks what was deducted even when the recipe changed after baking started', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 1.5, 'unit' => 'kg']);
    $order = Order::factory()->confirmed()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 1]);
    $transition = resolve(TransitionOrderStatus::class);
    $transition($order, OrderStatus::Baking);
    $recipe->inventoryIngredients()->updateExistingPivot($flour->id, ['quantity' => 0.3]);

    $transition($order, OrderStatus::Cancelled);

    expect($flour->fresh()->current_stock)->toBe('10.0000');
});

test('cancelling restocks what was deducted even when the recipe was deleted after baking started', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 1.5, 'unit' => 'kg']);
    $order = Order::factory()->confirmed()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 1]);
    $transition = resolve(TransitionOrderStatus::class);
    $transition($order, OrderStatus::Baking);
    $recipe->delete();

    $transition($order, OrderStatus::Cancelled);

    expect($flour->fresh()->current_stock)->toBe('10.0000');
});

test('two orders sharing scarce flour both reach Baking, leave stock negative and warn staff', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Bread Flour', 'unit' => 'kg', 'current_stock' => 1.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.6, 'unit' => 'kg']);
    $first = Order::factory()->confirmed()->create();
    $second = Order::factory()->confirmed()->create();
    OrderItem::factory()->for($first)->for($product)->create(['quantity' => 1]);
    OrderItem::factory()->for($second)->for($product)->create(['quantity' => 1]);
    $transition = resolve(TransitionOrderStatus::class);

    $transition($first, OrderStatus::Baking);
    $transition($second, OrderStatus::Baking);

    $notifications = test()->user->notifications()->get();
    expect($first->fresh()->status)->toBe(OrderStatus::Baking)
        ->and($second->fresh()->status)->toBe(OrderStatus::Baking)
        ->and($flour->fresh()->current_stock)->toBe('-0.2000')
        ->and($notifications)->toHaveCount(1)
        ->and($notifications->first()->data['body'])->toContain('Bread Flour', '0.20 kg')
        ->and($notifications->first()->data['title'])->toContain($second->order_number);
});

test('starting to bake with enough stock sends no warning', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.6, 'unit' => 'kg']);
    $order = Order::factory()->confirmed()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 1]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Baking);

    expect(test()->user->notifications()->count())->toBe(0);
});

test('a second delivered submit from a stale copy is refused and side effects run once', function () {
    Event::fake([OrderDelivered::class, OrderStatusChanged::class]);
    $order = Order::factory()->ready()->create();
    $stale = Order::query()->findOrFail($order->id);
    $transition = resolve(TransitionOrderStatus::class);
    $transition($order, OrderStatus::Delivered);

    expect(fn () => $transition($stale, OrderStatus::Delivered))
        ->toThrow(InvalidOrderTransitionException::class);

    Event::assertDispatchedTimes(OrderDelivered::class, 1);
    Event::assertDispatchedTimes(OrderStatusChanged::class, 1);
});

test('a second delivered submit from a stale copy sends one delivered email', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->ready()->create();
    $stale = Order::query()->findOrFail($order->id);
    $transition = resolve(TransitionOrderStatus::class);
    $transition($order, OrderStatus::Delivered);

    expect(fn () => $transition($stale, OrderStatus::Delivered))
        ->toThrow(InvalidOrderTransitionException::class);

    Mail::assertQueued(OrderStatusMail::class, 1);
});

test('cancellation from Pending does not restock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $butter = Ingredient::factory()->create(['current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($butter->id, ['quantity' => 1.0, 'unit' => 'kg']);

    $order = Order::factory()->pending()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    expect($butter->fresh()->current_stock)->toBe('5.0000')
        ->and($butter->stockAdjustments()->where('type', StockAdjustmentType::Restock)->count())->toBe(0);
});

test('cancellation from Confirmed does not restock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $eggs = Ingredient::factory()->create(['current_stock' => 12.00]);
    $recipe->inventoryIngredients()->attach($eggs->id, ['quantity' => 3.0, 'unit' => 'kg']);

    $order = Order::factory()->confirmed()->create();
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    expect($eggs->fresh()->current_stock)->toBe('12.0000')
        ->and($eggs->stockAdjustments()->where('type', StockAdjustmentType::Restock)->count())->toBe(0);
});

test('cancellation restores gift card balance and creates refund transaction', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->recycle(test()->user)
        ->create([
            'status' => OrderStatus::Confirmed,
            'gift_card_id' => $giftCard->id,
            'gift_card_amount' => 20.00,
        ]);
    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -20.00,
    ]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    expect($giftCard->fresh()->current_balance->dollars())->toBe(50.00);

    $refund = GiftCardTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', GiftCardTransactionType::Refund)
        ->first();

    expect($refund)->not->toBeNull()
        ->and($refund->amount->dollars())->toBe(20.00)
        ->and($refund->gift_card_id)->toBe($giftCard->id);
});
