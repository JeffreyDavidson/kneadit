<?php

use App\Enums\Financial\CouponType;
use App\Events\Customers\CustomerReferralCompleted;
use App\Listeners\Customers\SendCustomerReferralRewardEmailListener;
use App\Mail\Customers\CustomerReferralRewardMail;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    settings(['customer_referral_discount_dollars' => 15]);
});

test('mints a single-use fixed coupon for the referrer and queues the reward email', function () {
    $referrer = Customer::factory()->create(['email' => 'alice@example.com', 'name' => 'Alice']);
    $referee = Customer::factory()->create(['email' => 'bob@example.com', 'name' => 'Bob']);

    $referral = CustomerReferral::factory()->completed()->create([
        'referrer_customer_id' => $referrer->id,
        'referred_customer_id' => $referee->id,
    ]);

    (new SendCustomerReferralRewardEmailListener)->handle(new CustomerReferralCompleted($referral));

    $coupon = Coupon::query()->latest()->firstOrFail();
    expect($coupon->code)->toMatch('/^REF-[A-Z0-9]{6}$/')
        ->and($coupon->type)->toBe(CouponType::Fixed)
        ->and($coupon->fixed_amount?->dollars())->toBe(15.0)
        ->and($coupon->max_uses)->toBe(1)
        ->and($referral->refresh()->reward_coupon_id)->toBe($coupon->id);

    Mail::assertQueued(
        CustomerReferralRewardMail::class,
        fn (CustomerReferralRewardMail $mail): bool => $mail->hasTo('alice@example.com')
            && $mail->coupon->is($coupon),
    );
});
