<?php

use App\Models\Coupon;
use App\Models\Customer;
use App\Services\Customer\BirthdayService;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => setUpTenantTest());

it('creates a birthday coupon for a customer', function () {
    Date::setTestNow('2026-03-25');

    $customer = Customer::query()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'birthday' => '1990-03-25',
    ]);

    $service = new BirthdayService;
    $coupon = $service->findOrCreateBirthdayCoupon($customer, discountPercent: 15, validDays: 7);

    expect($coupon)->toBeInstanceOf(Coupon::class);
    expect($coupon->code)->toBe("BDAY-{$customer->id}-2026");
    expect((int) $coupon->value)->toBe(15);
    expect($coupon->expires_at->toDateString())->toBe('2026-04-01');
});
