<?php

use App\Enums\Marketing\EmailCampaignStatus;
use App\Mail\Platform\PlatformCampaignMail;
use App\Models\Engagement\EmailCampaign;
use App\Models\Engagement\EmailCampaignLog;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

beforeEach(function () {
    setUpCentralTest();
    Mail::fake();
    Date::setTestNow('2026-10-05 12:00');
    createTenant(['id' => 'one', 'email' => 'one@example.com']);
    createTenant(['id' => 'two', 'email' => 'two@example.com']);
});

test('sends a due scheduled platform campaign', function () {
    $campaign = EmailCampaign::factory()->create([
        'status' => EmailCampaignStatus::Scheduled,
        'scheduled_at' => now()->subMinute(),
    ]);

    artisan('platform:send-scheduled-campaigns')->assertSuccessful();

    Mail::assertQueued(PlatformCampaignMail::class, 2);
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(2);
});

test('does not send a campaign that is not due yet or is still a draft', function () {
    EmailCampaign::factory()->create([
        'status' => EmailCampaignStatus::Scheduled,
        'scheduled_at' => now()->addMinute(),
    ]);
    EmailCampaign::factory()->create();

    artisan('platform:send-scheduled-campaigns')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('sends a due campaign only once when the command runs twice', function () {
    EmailCampaign::factory()->create([
        'status' => EmailCampaignStatus::Scheduled,
        'scheduled_at' => now()->subMinute(),
    ]);

    artisan('platform:send-scheduled-campaigns')->assertSuccessful();
    artisan('platform:send-scheduled-campaigns')->assertSuccessful();

    Mail::assertQueued(PlatformCampaignMail::class, 2);
});

test('a stale sending campaign resumes and only emails owners without a log', function () {
    $campaign = EmailCampaign::factory()->sending()->create([
        'scheduled_at' => now()->subHours(3),
        'updated_at' => now()->subHours(2),
    ]);
    EmailCampaignLog::factory()->for($campaign, 'campaign')->create([
        'tenant_id' => 'one',
        'email' => 'one@example.com',
    ]);

    artisan('platform:send-scheduled-campaigns')->assertSuccessful();

    Mail::assertQueued(PlatformCampaignMail::class, 1);
    Mail::assertQueued(PlatformCampaignMail::class, fn (PlatformCampaignMail $mail): bool => $mail->hasTo('two@example.com'));
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(2);
});

test('leaves a recently claimed sending campaign alone', function () {
    $campaign = EmailCampaign::factory()->sending()->create(['updated_at' => now()->subMinutes(5)]);

    artisan('platform:send-scheduled-campaigns')->assertSuccessful();

    Mail::assertNothingQueued();
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sending);
});
