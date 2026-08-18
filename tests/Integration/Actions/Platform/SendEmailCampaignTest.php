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

beforeEach(fn () => setUpCentralTest());

function mockTenancyManager(): Mockery\MockInterface
{
    $mock = Mockery::mock(TenancyManager::class);
    $mock->shouldReceive('withinTenant')
        ->andReturnUsing(fn ($tenant, $callback) => $callback($tenant));
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
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(3)
        ->and($campaign->fresh()->sent_at)->not->toBeNull();
});

test('only targets tenants matching starter segment', function () {
    Mail::fake();
    $mock = mockTenancyManager();

    Tenant::factory()->starter()->create();
    Tenant::factory()->growth()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Starter,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    $mock->shouldHaveReceived('withinTenant')->once();
});

test('only targets tenants matching growth segment', function () {
    Mail::fake();
    $mock = mockTenancyManager();

    Tenant::factory()->starter()->create();
    Tenant::factory()->growth()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Growth,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    $mock->shouldHaveReceived('withinTenant')->once();
});

test('excludes inactive tenants from all segment', function () {
    Mail::fake();
    $mock = mockTenancyManager();

    Tenant::factory()->starter()->create();
    Tenant::factory()->starter()->inactive()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($campaign);

    $mock->shouldHaveReceived('withinTenant')->once();
});

test('targets only inactive tenants for inactive segment', function () {
    Mail::fake();
    $mock = mockTenancyManager();

    Tenant::factory()->starter()->create();
    Tenant::factory()->starter()->inactive()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Inactive,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    $mock->shouldHaveReceived('withinTenant')->once();
});

test('targets tenants on trial for trial segment', function () {
    Mail::fake();
    $mock = mockTenancyManager();

    Tenant::factory()->starter()->create();
    Tenant::factory()->onTrial()->create();
    Customer::factory()->count(2)->create();

    $campaign = EmailCampaign::factory()->create([
        'target_segment' => EmailCampaignSegment::Trial,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    $mock->shouldHaveReceived('withinTenant')->once();
});

test('deduplicates emails across tenants', function () {
    Mail::fake();
    mockTenancyManager();

    Tenant::factory()->starter()->count(2)->create();
    Customer::factory()->create(['email' => 'shared@example.com']);

    $campaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($campaign);

    Mail::assertQueued(CustomerBlastMail::class, 1);
    expect($campaign->fresh()->recipient_count)->toBe(1);
});
