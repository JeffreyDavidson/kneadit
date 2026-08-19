<?php

beforeEach(fn () => setUpCentralTest());

test('customers:backfill-referral-codes runs successfully with no tenants', function () {
    pendingArtisan('customers:backfill-referral-codes')
        ->expectsOutputToContain('Total customers updated: 0')
        ->assertSuccessful();
});

test('command source uses TenancyManager + GenerateCustomerReferralCode action', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/BackfillReferralCodesCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('GenerateCustomerReferralCode')
        ->toContain('whereNull(\'referral_code\')');
});
