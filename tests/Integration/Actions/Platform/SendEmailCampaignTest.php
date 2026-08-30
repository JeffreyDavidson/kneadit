<?php

use App\Actions\Platform\SendEmailCampaign;
use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Mail\Marketing\CustomerBlastMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\EmailCampaign;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Mail;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

function expectTenantCampaignProcessing(int $times = 1): void
{
    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->times($times)
        ->resolves(fn ($tenant, $callback) => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);
}

test('sends campaigns to unique customers and records delivery results', function () {
    Mail::fake();
    expectTenantCampaignProcessing(2);

    Tenant::factory()->starter()->count(2)->create();
    Customer::factory()->create(['email' => 'shared@example.com']);

    $deduplicatedCampaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($deduplicatedCampaign);

    Mail::assertQueued(CustomerBlastMail::class, 1);
    expect($deduplicatedCampaign->fresh()->recipient_count)->toBe(1);

    Mail::fake();
    expectTenantCampaignProcessing(2);
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'name' => 'Spring Campaign',
        'subject' => 'Spring Sale',
        'body' => '<p>20% off!</p>',
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    Mail::assertQueued(CustomerBlastMail::class, 3);
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(3)
        ->and($campaign->fresh()->sent_at)->not->toBeNull();
});

test('targets tenants for each campaign segment', function () {
    Mail::fake();

    Tenant::factory()->starter()->create();
    $growthTenant = Tenant::factory()->growth()->create();
    Customer::factory()->count(2)->create();

    expectTenantCampaignProcessing();
    $starterCampaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Starter,
    ]);

    resolve(SendEmailCampaign::class)($starterCampaign);

    expectTenantCampaignProcessing();
    $growthCampaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Growth,
    ]);

    resolve(SendEmailCampaign::class)($growthCampaign);

    $growthTenant->update(['is_active' => false]);

    expectTenantCampaignProcessing();
    $allCampaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($allCampaign);

    expectTenantCampaignProcessing();
    $inactiveCampaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Inactive,
    ]);

    resolve(SendEmailCampaign::class)($inactiveCampaign);

    Tenant::factory()->onTrial()->create();

    expectTenantCampaignProcessing();
    $trialCampaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Trial,
    ]);

    resolve(SendEmailCampaign::class)($trialCampaign);
});
