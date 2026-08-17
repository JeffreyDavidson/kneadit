<?php

use Illuminate\Support\Facades\Artisan;

beforeEach(fn () => setUpCentralTest());

test('customers:backfill-referral-codes runs successfully with no tenants', function () {
    expect(Artisan::call('customers:backfill-referral-codes'))->toBe(0)
        ->and(Artisan::output())->toContain('Total customers updated: 0');
});

test('command source uses TenancyManager + GenerateCustomerReferralCode action', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/BackfillReferralCodesCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('GenerateCustomerReferralCode')
        ->toContain('whereNull(\'referral_code\')');
});
