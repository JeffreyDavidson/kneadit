<?php

use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

beforeEach(fn () => setUpCentralTest());

test('campaigns:send-scheduled runs successfully with no tenants', function () {
    $this->artisan('campaigns:send-scheduled')->assertSuccessful();
});

test('only sends scheduled campaigns whose scheduled time has arrived', function () {
    $due = CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Scheduled,
        'scheduled_at' => now()->subMinute(),
    ]);
    CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Scheduled,
        'scheduled_at' => now()->addMinute(),
    ]);
    CustomerCampaign::factory()->create();

    $sender = Double::for(SendCustomerCampaign::class);
    $sender->expects('__invoke')
        ->with(Argument::satisfies(
            fn (mixed $campaign): bool => $campaign instanceof CustomerCampaign && $campaign->is($due),
        ))
        ->returns(4);
    app()->instance(SendCustomerCampaign::class, $sender);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('forEachTenant')
        ->resolves(function (callable $callback): int {
            $callback(
                new Tenant(['id' => 'test-tenant']),
                TenantSettings::resolve(),
            );

            return 0;
        });
    app()->instance(TenancyManager::class, $tenancyManager);

    $this->artisan('campaigns:send-scheduled')
        ->expectsOutput('test-tenant: campaign #' . $due->id . ' sent to 4 recipient(s)')
        ->assertSuccessful();
});
