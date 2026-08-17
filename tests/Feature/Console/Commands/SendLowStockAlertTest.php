<?php

use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('inventory:send-low-stock-alert command runs successfully with no tenants', function () {
    Mail::fake();

    pendingArtisan('inventory:send-low-stock-alert')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('command source uses TenancyManager for tenant context', function () {
    $source = file_get_contents(app_path('Console/Commands/Operations/SendLowStockAlertCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('lowStockAlertsEnabled');
});
