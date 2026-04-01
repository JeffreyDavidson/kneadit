<?php

namespace App\Actions\Platform;

use App\Enums\EmailCampaignStatus;
use App\Events\Marketing\CampaignEmailQueued;
use App\Models\Customer;
use App\Models\EmailCampaign;

class SendEmailCampaign
{
    public function __invoke(EmailCampaign $campaign): void
    {
        $campaign->update(['status' => EmailCampaignStatus::Sending]);

        $emails = Customer::query()->whereNotNull('email')
            ->distinct()
            ->pluck('email');

        foreach ($emails as $email) {
            CampaignEmailQueued::dispatch($email, $campaign->subject, $campaign->body);
        }

        $campaign->update([
            'status' => EmailCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $emails->count(),
        ]);
    }
}
