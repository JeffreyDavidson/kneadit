<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('carts:send-abandonment-emails runs successfully with no tenants', function () {
    Mail::fake();

    pendingArtisan('carts:send-abandonment-emails')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('command source uses TenancyManager + abandonedCartRecoveryEnabled', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/SendAbandonedCartRecoveryCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('abandonedCartRecoveryEnabled')
        ->toContain('recovery_sent_at')
        ->toContain('converted_at');
});
