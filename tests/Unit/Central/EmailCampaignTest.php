<?php

use App\Models\EmailCampaign;
use Carbon\Carbon;
beforeEach(fn () => setUpCentralTest());

test('can create campaign', function () {
    $campaign = EmailCampaign::create([
        'name' => 'Test Campaign',
        'subject' => 'Newsletter',
        'body' => 'Content here',
        'status' => 'draft',
    ]);

    expect(EmailCampaign::where('subject', 'Newsletter')->first())->not->toBeNull();
});

test('sent at is cast to datetime', function () {
    $campaign = EmailCampaign::create([
        'name' => 'Test',
        'subject' => 'Test',
        'body' => 'Body',
        'status' => 'sent',
        'sent_at' => '2026-01-01 12:00:00',
    ]);

    $campaign->refresh();
    expect($campaign->sent_at)->toBeInstanceOf(Carbon::class);
});

test('status can be updated to sent', function () {
    $campaign = EmailCampaign::create(['name' => 'T', 'subject' => 'T', 'body' => 'B', 'status' => 'draft']);
    $campaign->update(['status' => 'sent', 'sent_at' => now()]);

    expect($campaign->fresh()->status)->toBe('sent');
});
