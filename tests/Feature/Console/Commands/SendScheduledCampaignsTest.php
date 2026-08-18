<?php

beforeEach(fn () => setUpCentralTest());

test('campaigns:send-scheduled runs successfully with no tenants', function () {
    $this->artisan('campaigns:send-scheduled')->assertSuccessful();
});

test('command source uses TenancyManager + filters Scheduled campaigns', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/SendScheduledCampaignsCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('CustomerCampaignStatus::Scheduled')
        ->toContain('scheduled_at')
        ->toContain('SendCustomerCampaign');
});
