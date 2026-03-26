<?php

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

it('uppercases coupon code on creation via observer', function () {
    $coupon = Coupon::query()->create([
        'code' => 'summer25',
        'type' => CouponType::Percentage,
        'value' => 25,
        'is_active' => true,
    ]);

    expect($coupon->code)->toBe('SUMMER25');
});
