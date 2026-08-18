<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Customers\CustomerReferralStatus;
use App\Enums\Orders\DeliveryType;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Pipes\Orders\ApplyReferral;
use App\Pipes\Orders\OrderPipelineData;
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
    $payload->subtotal = 30.0;
    $payload->total = 30.0;

    return $payload;
}

test('applies the referral discount when a valid code is in session', function () {
    $referrer = Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');

    $payload = makeReferralPayload();
    $result = (new ApplyReferral(resolve(App\Services\Settings\TenantSettings::class)))->handle($payload, fn ($p) => $p);

    expect($result->referrer?->is($referrer))->toBeTrue()
        ->and($result->discountAmount)->toBe(10.0)
        ->and($result->total)->toBe(20.0);
});

test('skips when feature is disabled', function () {
    settings(['customer_referral_program_enabled' => false]);
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');

    $payload = makeReferralPayload();
    $result = (new ApplyReferral(resolve(App\Services\Settings\TenantSettings::class)))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull()
        ->and($result->discountAmount)->toBe(0.0);
});

test('skips when no code is in session', function () {
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);

    $payload = makeReferralPayload();
    $result = (new ApplyReferral(resolve(App\Services\Settings\TenantSettings::class)))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull();
});

test('rejects self-referral', function () {
    Customer::factory()->create(['email' => 'self@example.com', 'referral_code' => 'SELF1234']);
    Session::put('referral_code', 'SELF1234');

    $payload = makeReferralPayload(email: 'self@example.com');
    $result = (new ApplyReferral(resolve(App\Services\Settings\TenantSettings::class)))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull()
        ->and($result->discountAmount)->toBe(0.0);
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
    $result = (new ApplyReferral(resolve(App\Services\Settings\TenantSettings::class)))->handle($payload, fn ($p) => $p);

    expect($result->referrer)->toBeNull()
        ->and($result->discountAmount)->toBe(0.0);
});
