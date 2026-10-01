<?php

use App\Actions\Platform\SendEmailCampaign;
use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Exceptions\Platform\PlatformCampaignContextException;
use App\Mail\Platform\PlatformCampaignMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\EmailCampaign;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

/**
 * @param  list<string>  $expected
 */
function expectCampaignSentTo(EmailCampaign $campaign, array $expected): void
{
    $recipients = [];

    Mail::assertQueued(PlatformCampaignMail::class, function (PlatformCampaignMail $mail) use (&$recipients): bool {
        $recipients[] = $mail->to[0]['address'];

        return true;
    });

    expect($recipients)->toEqualCanonicalizing($expected)
        ->and($campaign->fresh()->recipient_count)->toBe(count($expected));
}

test('sends a campaign to the bakery owners of the segment and not to their customers', function () {
    Mail::fake();

    Tenant::factory()->pro()->create(['email' => 'owner-one@example.com']);
    Tenant::factory()->pro()->create(['email' => 'owner-two@example.com']);
    Tenant::factory()->starter()->create(['email' => 'starter-owner@example.com']);
    Customer::factory()->create(['email' => 'customer-one@example.com']);
    Customer::factory()->create(['email' => 'customer-two@example.com']);

    $campaign = EmailCampaign::factory()->create([
        'subject' => 'Platform update',
        'body' => '<p>New features are live.</p>',
        'target_segment' => EmailCampaignSegment::Pro,
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    expectCampaignSentTo($campaign, ['owner-one@example.com', 'owner-two@example.com']);
    Mail::assertNotQueued(PlatformCampaignMail::class, fn (PlatformCampaignMail $mail): bool => $mail->hasTo('customer-one@example.com')
        || $mail->hasTo('customer-two@example.com'));
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->fresh()->sent_at)->not->toBeNull();
});

test('sends one email when bakeries share an owner address', function () {
    Mail::fake();

    Tenant::factory()->count(2)->create(['email' => 'shared-owner@example.com']);

    $campaign = EmailCampaign::factory()->create();

    resolve(SendEmailCampaign::class)($campaign);

    expectCampaignSentTo($campaign, ['shared-owner@example.com']);
});

test('sends the campaign subject and body in the platform mail', function () {
    Mail::fake();

    Tenant::factory()->create(['email' => 'owner@example.com']);
    $campaign = EmailCampaign::factory()->create([
        'subject' => 'Platform update',
        'body' => '<p>New features are live.</p>',
    ]);

    resolve(SendEmailCampaign::class)($campaign);

    Mail::assertQueued(PlatformCampaignMail::class, fn (PlatformCampaignMail $mail): bool => $mail->campaignSubject === 'Platform update'
        && $mail->campaignBody === '<p>New features are live.</p>');
});

test('targets the bakery owners of each campaign segment', function (EmailCampaignSegment $segment, array $expected) {
    Mail::fake();

    Tenant::factory()->starter()->create(['email' => 'starter@example.com']);
    Tenant::factory()->growth()->create(['email' => 'growth@example.com']);
    Tenant::factory()->pro()->create(['email' => 'pro@example.com']);
    Tenant::factory()->pro()->onTrial()->create(['email' => 'trial@example.com']);
    Tenant::factory()->inactive()->create(['email' => 'inactive@example.com']);

    $campaign = EmailCampaign::factory()->create(['target_segment' => $segment]);

    resolve(SendEmailCampaign::class)($campaign);

    expectCampaignSentTo($campaign, $expected);
})->with([
    'all active bakeries' => [EmailCampaignSegment::All, ['starter@example.com', 'growth@example.com', 'pro@example.com', 'trial@example.com']],
    'starter' => [EmailCampaignSegment::Starter, ['starter@example.com']],
    'growth' => [EmailCampaignSegment::Growth, ['growth@example.com']],
    'pro' => [EmailCampaignSegment::Pro, ['pro@example.com', 'trial@example.com']],
    'trial' => [EmailCampaignSegment::Trial, ['trial@example.com']],
    'inactive' => [EmailCampaignSegment::Inactive, ['inactive@example.com']],
]);

test('refuses to send a campaign inside a tenant context', function () {
    Mail::fake();

    Tenant::factory()->create(['email' => 'owner@example.com']);
    Customer::factory()->create(['email' => 'customer@example.com']);
    $campaign = EmailCampaign::factory()->create();

    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize(new Tenant(['id' => 'campaign-context-bakery']));

    expect(fn () => resolve(SendEmailCampaign::class)($campaign))
        ->toThrow(PlatformCampaignContextException::class);

    Mail::assertNothingQueued();
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Draft)
        ->and($campaign->fresh()->recipient_count)->toBe(0);
});
