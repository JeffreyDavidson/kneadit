<?php

use App\Actions\Customers\CreateBirthdayCoupon;
use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => setUpTenantTest());

test('creates birthday coupon for customer', function () {
    $customer = Customer::factory()->create();

    $coupon = resolve(CreateBirthdayCoupon::class)($customer, 15);

    expect($coupon)
        ->toBeInstanceOf(Coupon::class)
        ->code->toMatch('/^BDAY-[A-Z0-9]{8}$/')
        ->type->toBe(CouponType::Percentage)
        ->max_uses->toBe(1)
        ->used_count->toBe(0)
        ->is_active->toBeTrue()
        ->and($coupon->percentage->value())->toBe(15.0);
});

test('returns null when discount percent is zero', function () {
    $customer = Customer::factory()->create();

    $result = resolve(CreateBirthdayCoupon::class)($customer, 0);

    expect($result)->toBeNull();
});

test('returns null when discount percent is negative', function () {
    $customer = Customer::factory()->create();

    $result = resolve(CreateBirthdayCoupon::class)($customer, -10);

    expect($result)->toBeNull();
});

test('returns existing coupon on retry (idempotent)', function () {
    $customer = Customer::factory()->create();

    $first = resolve(CreateBirthdayCoupon::class)($customer, 15);
    $second = resolve(CreateBirthdayCoupon::class)($customer, 20);

    expect($first->id)->toBe($second->id)
        ->and(Coupon::query()->count())->toBe(1);
});

test('uses custom valid days', function () {
    $customer = Customer::factory()->create();

    $coupon = resolve(CreateBirthdayCoupon::class)($customer, 10, 14);

    expect($coupon->expires_at->toDateString())
        ->toBe(now()->addDays(14)->toDateString());
});

test('birthday codes are random rather than built from the customer id', function () {
    $customers = Customer::factory()->count(2)->create();

    $codes = $customers->map(fn (Customer $customer) => resolve(CreateBirthdayCoupon::class)($customer, 15)->code);

    expect($codes[0])->not->toBe($codes[1])
        ->and($codes->every(fn (string $code) => preg_match('/^BDAY-\d+-\d{4}$/', $code) === 0))->toBeTrue();
});

test('creates one coupon per customer per year, but a new one the next year', function () {
    $customer = Customer::factory()->create();
    $other = Customer::factory()->create();

    Date::setTestNow('2026-03-25 12:00');
    $first = resolve(CreateBirthdayCoupon::class)($customer, 15);
    Date::setTestNow('2026-03-26 12:00');
    $again = resolve(CreateBirthdayCoupon::class)($customer, 15);
    $otherCustomers = resolve(CreateBirthdayCoupon::class)($other, 15);
    Date::setTestNow('2027-03-25 12:00');
    $nextYear = resolve(CreateBirthdayCoupon::class)($customer, 15);

    expect($again->id)->toBe($first->id)
        ->and($otherCustomers->id)->not->toBe($first->id)
        ->and($nextYear->id)->not->toBe($first->id)
        ->and(Coupon::query()->count())->toBe(3);
});

test('starts and expires on the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 02:00');
    $customer = Customer::factory()->create();

    $coupon = resolve(CreateBirthdayCoupon::class)($customer, 15, 7);

    expect($coupon->starts_at->toDateString())->toBe('2026-10-05')
        ->and($coupon->expires_at->toDateString())->toBe('2026-10-12');
});
