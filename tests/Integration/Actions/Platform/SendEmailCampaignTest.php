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

function mockTenancyManager(?int $expectedCalls = null): TenancyManager
{
    $mock = Double::for(TenancyManager::class);
    $expectation = $mock->expects('withinTenant')
        ->resolves(fn (mixed ...$arguments): mixed => isset($arguments[1]) && is_callable($arguments[1]) ? $arguments[1]($arguments[0] ?? null) : null);

    if ($expectedCalls !== null) {
        $expectation->times($expectedCalls);
    }
    app()->instance(TenancyManager::class, $mock);

    return $mock;
}

test('sends campaign to all customers across active tenants', function () {
    Mail::fake();
    mockTenancyManager();

    Tenant::factory()->starter()->create();
    Customer::factory()->count(3)->create();

    $campaign = EmailCampaign::factory()->create([
        'name' => 'Spring Campaign',
        'subject' => 'Spring Sale',
        'body' => '<p>20% off!</p>',
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    Mail::assertQueued(CustomerBlastMail::class, 3);
    expect($campaign->refresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->refresh()->recipient_count)->toBe(3)
        ->and($campaign->refresh()->sent_at)->not->toBeNull();
});

test('only targets tenants matching starter segment', function () {
    Mail::fake();
    mockTenancyManager(expectedCalls: 1);

    Tenant::factory()->starter()->create();
    Tenant::factory()->growth()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Starter,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

});

test('only targets tenants matching growth segment', function () {
    Mail::fake();
    mockTenancyManager(expectedCalls: 1);

    Tenant::factory()->starter()->create();
    Tenant::factory()->growth()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Growth,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

});

test('excludes inactive tenants from all segment', function () {
    Mail::fake();
    mockTenancyManager(expectedCalls: 1);

    Tenant::factory()->starter()->create();
    Tenant::factory()->starter()->inactive()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($campaign);

});

test('targets only inactive tenants for inactive segment', function () {
    Mail::fake();
    mockTenancyManager(expectedCalls: 1);

    Tenant::factory()->starter()->create();
    Tenant::factory()->starter()->inactive()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Inactive,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

});

test('targets tenants on trial for trial segment', function () {
    Mail::fake();
    mockTenancyManager(expectedCalls: 1);

    Tenant::factory()->starter()->create();
    Tenant::factory()->onTrial()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Trial,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

});

test('deduplicates emails across tenants', function () {
    Mail::fake();
    mockTenancyManager();

    Tenant::factory()->starter()->count(2)->create();
    Customer::factory()->create(['email' => 'shared@example.com']);

    $campaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($campaign);

    Mail::assertQueued(CustomerBlastMail::class, 1);
    expect($campaign->refresh()->recipient_count)->toBe(1);
});
