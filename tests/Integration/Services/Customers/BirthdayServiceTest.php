<?php

use App\Actions\Customers\CreateBirthdayCoupon;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => setUpTenantTest());

it('creates a birthday coupon for a customer', function () {
    Date::setTestNow('2026-03-25');

    $customer = Customer::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'birthday' => '1990-03-25',
    ]);

    $coupon = app(CreateBirthdayCoupon::class)($customer, discountPercent: 15, validDays: 7);

    expect($coupon)->toBeInstanceOf(Coupon::class)->and($coupon->code)->toBe("BDAY-{$customer->id}-2026")->and((int) $coupon->value)->toBe(15)->and($coupon->expires_at->toDateString())->toBe('2026-04-01');
});
