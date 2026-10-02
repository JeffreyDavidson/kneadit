<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Customers\CustomerReferralStatus;
use App\Enums\Orders\DeliveryType;
use App\Events\Customers\CustomerReferralCompleted;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    settings([
        'customer_referral_program_enabled' => true,
        'customer_referral_discount_dollars' => 10,
    ]);
});

test('records a pending referral at placement without rewarding the referrer', function () {
    Event::fake([CustomerReferralCompleted::class]);
    $referrer = Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    $product = Product::factory()->create(['price' => 30.00]);
    Session::put('referral_code', 'ABC12345');

    $order = resolve(CreateOrder::class)(CreateOrderData::fromArray([
        'customer_name' => 'Bob',
        'customer_email' => 'bob@example.com',
        'delivery_date' => now()->addDays(5)->toDateString(),
        'delivery_type' => DeliveryType::Pickup->value,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]));

    $referral = CustomerReferral::query()->sole();

    expect($order->total->dollars())->toBe(20.0)
        ->and($referral->status)->toBe(CustomerReferralStatus::Pending)
        ->and($referral->completed_at)->toBeNull()
        ->and($referral->referrer_customer_id)->toBe($referrer->id)
        ->and($referral->order_id)->toBe($order->id)
        ->and($referral->reward_coupon_id)->toBeNull()
        ->and(Coupon::query()->where('code', 'like', 'REF-%')->exists())->toBeFalse();

    Event::assertNotDispatched(CustomerReferralCompleted::class);
});
