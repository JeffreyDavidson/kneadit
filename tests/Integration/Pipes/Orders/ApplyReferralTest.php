<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Customers\CustomerReferralStatus;
use App\Enums\Orders\DeliveryType;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Orders\Order;
use App\Pipes\Orders\ApplyReferral;
use App\Pipes\Orders\OrderPipelineData;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings([
        'customer_referral_program_enabled' => true,
        'customer_referral_discount_dollars' => 10,
    ]);
});

function makeReferralPayload(string $email = 'newcomer@example.com'): OrderPipelineData
{
    $payload = new OrderPipelineData(new CreateOrderData(
        customerName: 'Newcomer',
        customerEmail: $email,
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => 1, 'quantity' => 1]],
    ));
    $payload->subtotal = Money::fromDollars(30.0);
    $payload->recalculateTotal();

    return $payload;
}

test('applies the referral discount when a valid code is in session', function () {
    $referrer = Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');

    $payload = makeReferralPayload();
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer?->is($referrer))->toBeTrue()
        ->and($result->discountAmount->dollars())->toBe(10.0)
        ->and($result->total->dollars())->toBe(20.0);
});

test('skips when feature is disabled', function () {
    settings(['customer_referral_program_enabled' => false]);
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');

    $payload = makeReferralPayload();
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull()
        ->and($result->discountAmount->dollars())->toBe(0.0);
});

test('skips when no code is in session', function () {
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);

    $payload = makeReferralPayload();
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull();
});

test('rejects self-referral', function () {
    Customer::factory()->create(['email' => 'self@example.com', 'referral_code' => 'SELF1234']);
    Session::put('referral_code', 'SELF1234');

    $payload = makeReferralPayload(email: 'self@example.com');
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull()
        ->and($result->discountAmount->dollars())->toBe(0.0);
});

test('rejects when the referee has already been referred before', function () {
    $referrer = Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    $referee = Customer::factory()->create(['email' => 'bob@example.com']);
    CustomerReferral::factory()->create([
        'referrer_customer_id' => $referrer->id,
        'referred_customer_id' => $referee->id,
        'status' => CustomerReferralStatus::Completed,
    ]);

    Session::put('referral_code', 'ABC12345');

    $payload = makeReferralPayload(email: 'bob@example.com');
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull()
        ->and($result->discountAmount->dollars())->toBe(0.0);
});

dataset('referral eligibility by order history', [
    'brand-new email' => [null, true],
    'delivered past order' => ['delivered', false],
    'pending past order' => ['pending', false],
    'only a cancelled past order' => ['cancelled', true],
]);

test('applies the referral discount only to emails with no prior non-cancelled order', function (?string $priorOrderState, bool $discounted) {
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');

    if ($priorOrderState !== null) {
        Order::factory()
            ->for(Customer::factory()->create(['email' => 'returning@example.com']))
            ->{$priorOrderState}()
            ->create();
    }

    $payload = makeReferralPayload(email: 'returning@example.com');
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer !== null)->toBe($discounted)
        ->and($result->discountAmount->dollars())->toBe($discounted ? 10.0 : 0.0);
})->with('referral eligibility by order history');

test('allows a new referral when the earlier referral for the same customer was cancelled', function () {
    $referrer = Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    $referee = Customer::factory()->create(['email' => 'bob@example.com']);
    CustomerReferral::factory()->create([
        'referrer_customer_id' => $referrer->id,
        'referred_customer_id' => $referee->id,
        'status' => CustomerReferralStatus::Cancelled,
    ]);
    Session::put('referral_code', 'ABC12345');

    $payload = makeReferralPayload(email: 'bob@example.com');
    $result = new ApplyReferral(resolve(TenantSettings::class))->handle($payload, fn ($p) => $p);

    expect($result->referrer?->is($referrer))->toBeTrue();
});
