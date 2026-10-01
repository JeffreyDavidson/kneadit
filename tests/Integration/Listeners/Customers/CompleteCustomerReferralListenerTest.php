<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Customers\CustomerReferralStatus;
use App\Enums\Orders\OrderStatus;
use App\Events\Customers\CustomerReferralCompleted;
use App\Events\Orders\OrderDelivered;
use App\Listeners\Customers\CompleteCustomerReferralListener;
use App\Mail\Customers\CustomerReferralRewardMail;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    settings(['customer_referral_discount_dollars' => 15]);
    test()->referrer = Customer::factory()->create(['email' => 'alice@example.com']);
    test()->order = Order::factory()->ready()->create();
    test()->referral = CustomerReferral::factory()->create([
        'referrer_customer_id' => test()->referrer->id,
        'referred_customer_id' => test()->order->customer_id,
        'order_id' => test()->order->id,
    ]);
});

test('completes the pending referral when the order is delivered', function () {
    Date::setTestNow('2026-10-01 10:00');
    Event::fake([CustomerReferralCompleted::class]);

    resolve(CompleteCustomerReferralListener::class)->handle(
        new OrderDelivered(test()->order, OrderStatus::Ready),
    );

    $referral = test()->referral->fresh();

    expect($referral->status)->toBe(CustomerReferralStatus::Completed)
        ->and($referral->completed_at?->toDateTimeString())->toBe('2026-10-01 10:00:00');

    Event::assertDispatched(
        CustomerReferralCompleted::class,
        fn (CustomerReferralCompleted $event): bool => $event->referral->is(test()->referral),
    );
});

test('ignores referrals that are no longer pending', function (CustomerReferralStatus $status) {
    Event::fake([CustomerReferralCompleted::class]);
    test()->referral->update(['status' => $status]);

    resolve(CompleteCustomerReferralListener::class)->handle(
        new OrderDelivered(test()->order, OrderStatus::Ready),
    );

    expect(test()->referral->fresh()->status)->toBe($status);

    Event::assertNotDispatched(CustomerReferralCompleted::class);
})->with([
    'completed' => CustomerReferralStatus::Completed,
    'cancelled' => CustomerReferralStatus::Cancelled,
]);

test('does nothing for an order that has no referral', function () {
    Event::fake([CustomerReferralCompleted::class]);

    resolve(CompleteCustomerReferralListener::class)->handle(
        new OrderDelivered(Order::factory()->ready()->create(), OrderStatus::Ready),
    );

    Event::assertNotDispatched(CustomerReferralCompleted::class);
});

test('delivering the order mints one reward coupon and mails the referrer once', function () {
    resolve(TransitionOrderStatus::class)(test()->order, OrderStatus::Delivered);

    $referral = test()->referral->fresh();

    expect($referral->status)->toBe(CustomerReferralStatus::Completed)
        ->and($referral->reward_coupon_id)->not->toBeNull()
        ->and(Coupon::query()->where('code', 'like', 'REF-%')->count())->toBe(1);

    Mail::assertQueued(
        CustomerReferralRewardMail::class,
        fn (CustomerReferralRewardMail $mail): bool => $mail->hasTo('alice@example.com')
            && $mail->coupon->is($referral->rewardCoupon),
    );
    Mail::assertQueued(CustomerReferralRewardMail::class, 1);
});
