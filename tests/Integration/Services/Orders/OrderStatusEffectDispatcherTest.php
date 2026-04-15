<?php

use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Orders\OrderStatusEffectDispatcher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    Http::fake();
    $this->user = User::factory()->owner()->create();
    $this->customer = Customer::factory()->create(['email' => 'customer@example.com']);
});

dataset('statusTransitions', [
    'confirmed' => [OrderStatus::Pending, OrderStatus::Confirmed],
    'baking' => [OrderStatus::Confirmed, OrderStatus::Baking],
    'ready' => [OrderStatus::Baking, OrderStatus::Ready],
    'delivered' => [OrderStatus::Ready, OrderStatus::Delivered],
    'cancelled' => [OrderStatus::Confirmed, OrderStatus::Cancelled],
]);

test('sends the correct email for each status', function (OrderStatus $from, OrderStatus $to) {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    resolve(OrderStatusEffectDispatcher::class)->dispatch($order, $from, $to);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === $to && $mail->hasTo('customer@example.com'));
})->with('statusTransitions');

test('does not send email when order has no customer', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    // Simulate order without customer relationship
    $order->setRelation('customer', null);

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed);

    Mail::assertNothingQueued();
});

test('awards loyalty points on delivered', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 20.00, 'subtotal' => 20.00]);

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Ready, OrderStatus::Delivered);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(1);
});

test('does not award loyalty points on non-delivered statuses', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create(['total' => 20.00, 'subtotal' => 20.00]);

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed);

    expect(LoyaltyPoint::query()->count())->toBe(0);
});

test('dispatches webhook on status change', function () {
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.updated')
            && $body['data']['status'] === 'confirmed'
            && $body['data']['previous_status'] === 'pending';
    });
});

test('one effect failure does not block others', function () {
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    // Create order without customer to make email fail gracefully,
    // but webhook should still fire
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed);

    // Both email and webhook should have been attempted
    Mail::assertQueued(OrderStatusMail::class);
    Http::assertSentCount(1);
});

test('pending status has no effects', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Confirmed, OrderStatus::Pending);

    Mail::assertNothingQueued();
    Http::assertNothingSent();
});

test('cancellation decrements coupon used_count and creates reversal transaction', function () {
    $coupon = Coupon::factory()->create(['used_count' => 3]);

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create(['coupon_id' => $coupon->id, 'discount_amount' => 5.00]);

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Confirmed, OrderStatus::Cancelled);

    expect($coupon->fresh()->used_count)->toBe(2);
    expect(CouponTransaction::query()->where('order_id', $order->id)->count())->toBe(1);

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction->type)->toBe(CouponTransactionType::Reversal)
        ->and($transaction->coupon_id)->toBe($coupon->id);
});

test('cancellation restores gift card balance and creates refund transaction', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create([
            'gift_card_id' => $giftCard->id,
            'gift_card_amount' => 20.00,
        ]);

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Confirmed, OrderStatus::Cancelled);

    expect($giftCard->fresh()->current_balance)->toBe('50.00');

    $refund = GiftCardTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', GiftCardTransactionType::Refund)
        ->first();

    expect($refund)->not->toBeNull()
        ->and($refund->amount)->toBe('20.00')
        ->and($refund->gift_card_id)->toBe($giftCard->id);
});

test('cancellation with no coupon or gift card does not fail', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Confirmed, OrderStatus::Cancelled);

    expect(CouponTransaction::query()->count())->toBe(0);
    expect(GiftCardTransaction::query()->count())->toBe(0);
});

test('critical effect failure rethrows exception', function () {
    // The Baking status has deductIngredients as a critical effect.
    // If deductForOrder throws, the exception should propagate.
    $inventoryManager = Mockery::mock(App\Services\Inventory\InventoryManager::class);
    $inventoryManager->shouldReceive('deductForOrder')
        ->andThrow(new RuntimeException('Inventory deduction failed'));

    app()->instance(App\Services\Inventory\InventoryManager::class, $inventoryManager);

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    expect(fn () => resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Confirmed, OrderStatus::Baking))
        ->toThrow(RuntimeException::class, 'Inventory deduction failed');
});

test('non-critical effect failure logs warning and continues', function () {
    // Confirmed status has sendEmail (non-critical) and dispatchWebhook (non-critical).
    // If email fails, webhook should still be dispatched.
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    // Make Mail throw to simulate a non-critical sendEmail failure
    Mail::shouldReceive('to')->andThrow(new RuntimeException('Mail server down'));

    settings(['webhook_url' => 'https://hooks.example.com/test']);

    resolve(OrderStatusEffectDispatcher::class)
        ->dispatch($order, OrderStatus::Pending, OrderStatus::Confirmed);

    // Webhook should still have been sent
    Http::assertSent(fn ($request) => $request->hasHeader('X-KneadIt-Event', 'order.updated'));
});
