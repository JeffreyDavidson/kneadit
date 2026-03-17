<?php

use App\Http\Controllers\StripeConnectController;
use App\Models\Setting;

beforeEach(function () {
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('get account status returns null when no connect id', function () {
    expect(StripeConnectController::getAccountStatus())->toBeNull();
});

test('get account status returns null when connect id is empty', function () {
    Setting::set('stripe_connect_id', '');

    expect(StripeConnectController::getAccountStatus())->toBeNull();
});

test('get account status returns null when stripe api fails', function () {
    Setting::set('stripe_connect_id', 'acct_invalid_12345');

    config(['cashier.secret' => 'sk_test_fake_key']);

    $result = StripeConnectController::getAccountStatus();

    expect($result)->toBeNull();
});
