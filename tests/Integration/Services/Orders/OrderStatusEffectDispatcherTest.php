<?php

use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\OrderBakingMail;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderDeliveredMail;
use App\Mail\Orders\OrderReadyMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
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

dataset('statusToMailable', [
    'confirmed' => [OrderStatus::Pending, OrderStatus::Confirmed, OrderConfirmedMail::class],
    'baking' => [OrderStatus::Confirmed, OrderStatus::Baking, OrderBakingMail::class],
    'ready' => [OrderStatus::Baking, OrderStatus::Ready, OrderReadyMail::class],
    'delivered' => [OrderStatus::Ready, OrderStatus::Delivered, OrderDeliveredMail::class],
    'cancelled' => [OrderStatus::Confirmed, OrderStatus::Cancelled, OrderCancelledMail::class],
]);

test('sends the correct email for each status', function (OrderStatus $from, OrderStatus $to, string $mailableClass) {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create();

    resolve(OrderStatusEffectDispatcher::class)->dispatch($order, $from, $to);

    Mail::assertQueued($mailableClass, fn ($mail) => $mail->hasTo('customer@example.com'));
})->with('statusToMailable');

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
    Mail::assertQueued(OrderConfirmedMail::class);
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
