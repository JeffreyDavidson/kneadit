<?php

use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\CustomerCampaignLog;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Stancl\Tenancy\Tenancy;

beforeEach(fn () => setUpCentralTest());

test('campaigns:send-scheduled runs successfully with no tenants', function () {
    $this->artisan('campaigns:send-scheduled')->assertSuccessful();
});

test('campaigns:send-scheduled resets stale sending campaigns for retry', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/SendScheduledCampaignsCommand.php'));

    expect($source)
        ->toContain('CustomerCampaignStatus::Sending')
        ->toContain('now()->subHour()')
        ->toContain('CustomerCampaignStatus::Scheduled');
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
        ->expectsOutput('test-tenant: campaign #'.$due->id.' sent to 4 recipient(s)')
        ->assertSuccessful();
});

/**
 * Runs the real command and TenancyManager; the Tenancy double only swaps the
 * database switch, because the test database holds every tenant's tables.
 */
function runScheduledCampaignsForOneTenant(): void
{
    createTenant(['id' => 'test-tenant']);

    $tenancy = Double::for(Tenancy::class);
    $tenancy->allows('initialize');
    $tenancy->allows('end');
    app()->instance(Tenancy::class, $tenancy);

    test()->artisan('campaigns:send-scheduled')->assertSuccessful();
}

test('a stale sending campaign resumes and only emails recipients without a log', function (?string $scheduledAt) {
    Mail::fake();
    Date::setTestNow('2026-10-05 12:00');

    $customers = Customer::factory()->count(3)->create();
    $customers->each(fn (Customer $customer) => Order::factory()->for($customer)->paid()->create([
        'delivery_date' => '2026-09-30',
    ]));
    $campaign = CustomerCampaign::factory()->sending()->create([
        'scheduled_at' => $scheduledAt,
        'updated_at' => now()->subHours(2),
    ]);
    CustomerCampaignLog::factory()->for($campaign, 'campaign')->create(['customer_email' => $customers[0]->email]);

    runScheduledCampaignsForOneTenant();

    Mail::assertQueued(CustomerCampaignMail::class, 2);
    Mail::assertNotQueued(CustomerCampaignMail::class, fn (CustomerCampaignMail $mail): bool => $mail->hasTo($customers[0]->email));
    expect($campaign->fresh()->status)->toBe(CustomerCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(3)
        ->and($campaign->logs()->count())->toBe(3);
})->with([
    'sent now, never scheduled' => [null],
    'scheduled earlier' => ['2026-10-05 09:00:00'],
]);

test('a stale reset gives a campaign without a schedule one so it is picked up', function () {
    Date::setTestNow('2026-10-05 12:00');
    $campaign = CustomerCampaign::factory()->sending()->create(['updated_at' => now()->subHours(2)]);
    $sender = Double::for(SendCustomerCampaign::class);
    $sender->allows('__invoke')->returns(0);
    app()->instance(SendCustomerCampaign::class, $sender);

    runScheduledCampaignsForOneTenant();

    expect($campaign->fresh()->scheduled_at?->toDateTimeString())->toBe('2026-10-05 12:00:00');
});
