<?php

namespace Tests\Unit\Central;

use App\Models\EmailCampaign;
use Tests\CentralTestCase;

class EmailCampaignTest extends CentralTestCase
{
    public function test_can_create_campaign(): void
    {
        $campaign = EmailCampaign::create([
            'name' => 'Test Campaign',
            'subject' => 'Newsletter',
            'body' => 'Content here',
            'status' => 'draft',
        ]);

        $this->assertNotNull(EmailCampaign::where('subject', 'Newsletter')->first());
    }

    public function test_sent_at_is_cast_to_datetime(): void
    {
        $campaign = EmailCampaign::create([
            'name' => 'Test',
            'subject' => 'Test',
            'body' => 'Body',
            'status' => 'sent',
            'sent_at' => '2026-01-01 12:00:00',
        ]);

        $campaign->refresh();
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->sent_at);
    }

    public function test_status_can_be_updated_to_sent(): void
    {
        $campaign = EmailCampaign::create(['name' => 'T', 'subject' => 'T', 'body' => 'B', 'status' => 'draft']);
        $campaign->update(['status' => 'sent', 'sent_at' => now()]);

        $this->assertEquals('sent', $campaign->fresh()->status);
    }
}
