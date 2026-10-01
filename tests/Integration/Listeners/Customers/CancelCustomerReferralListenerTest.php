<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Customers\CustomerReferralStatus;
use App\Enums\Orders\OrderStatus;
use App\Events\Customers\CustomerReferralCompleted;
use App\Events\Orders\OrderCancelled;
use App\Listeners\Customers\CancelCustomerReferralListener;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    test()->order = Order::factory()->pending()->create();
    test()->referral = CustomerReferral::factory()->create([
        'referred_customer_id' => test()->order->customer_id,
        'order_id' => test()->order->id,
    ]);
});

test('cancels the pending referral when the order is cancelled', function () {
    resolve(CancelCustomerReferralListener::class)->handle(
        new OrderCancelled(test()->order, OrderStatus::Pending),
    );

    $referral = test()->referral->fresh();

    expect($referral->status)->toBe(CustomerReferralStatus::Cancelled)
        ->and($referral->completed_at)->toBeNull();
});

test('leaves a completed referral untouched', function () {
    test()->referral->update(['status' => CustomerReferralStatus::Completed]);

    resolve(CancelCustomerReferralListener::class)->handle(
        new OrderCancelled(test()->order, OrderStatus::Pending),
    );

    expect(test()->referral->fresh()->status)->toBe(CustomerReferralStatus::Completed);
});

test('cancelling the order cancels the referral without issuing a reward', function () {
    Event::fake([CustomerReferralCompleted::class]);

    resolve(TransitionOrderStatus::class)(test()->order, OrderStatus::Cancelled);

    expect(test()->referral->fresh()->status)->toBe(CustomerReferralStatus::Cancelled)
        ->and(test()->referral->fresh()->reward_coupon_id)->toBeNull()
        ->and(Coupon::query()->where('code', 'like', 'REF-%')->exists())->toBeFalse();

    Event::assertNotDispatched(CustomerReferralCompleted::class);
});
