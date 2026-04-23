<?php

namespace App\Actions\Marketing;

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Engagement\CustomerCampaign;
use App\Services\Customers\ResolveCampaignRecipients;
use Illuminate\Support\Facades\Mail;

/**
 * Sends a customer campaign to all recipients matching its target segment.
 * Idempotent guard: refuses to re-send a campaign that's already Sent.
 */
class SendCustomerCampaign
{
    public function __construct(
        private ResolveCampaignRecipients $resolveRecipients,
    ) {}

    public function __invoke(CustomerCampaign $campaign): int
    {
        if ($campaign->status === CustomerCampaignStatus::Sent) {
            return 0;
        }

        $campaign->forceFill(['status' => CustomerCampaignStatus::Sending])->save();

        $recipients = ($this->resolveRecipients)($campaign->target_segment);

        $sent = 0;
        foreach ($recipients as $customer) {
            if (! $customer->email) {
                continue;
            }

            Mail::to($customer->email)->queue(new CustomerCampaignMail($campaign));
            $sent++;
        }

        $campaign->forceFill([
            'status' => CustomerCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $sent,
        ])->save();

        return $sent;
    }
}
