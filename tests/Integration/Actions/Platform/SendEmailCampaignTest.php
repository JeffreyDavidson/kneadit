<?php

use App\Actions\Platform\SendEmailCampaign;
use App\Enums\EmailCampaignStatus;
use App\Mail\CustomerBlast;
use App\Models\Customer;
use App\Models\EmailCampaign;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpCentralTest());

test('sends campaign to all customers and updates status', function () {
    Mail::fake();

    $campaign = EmailCampaign::query()->create([
        'name' => 'Spring Campaign',
        'subject' => 'Spring Sale',
        'body' => '<p>20% off!</p>',
        'status' => EmailCampaignStatus::Draft,
    ]);

    Customer::factory()->count(3)->create();

    app(SendEmailCampaign::class)($campaign);

    Mail::assertQueued(CustomerBlast::class, 3);
    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(3)
        ->and($campaign->fresh()->sent_at)->not->toBeNull();
});
