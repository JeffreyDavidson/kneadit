<?php

namespace App\Actions\Marketing;

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\CustomerCampaignLog;
use App\Services\Customers\ResolveCampaignRecipients;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Sends a customer campaign to all recipients matching its target segment.
 * Idempotent guard: refuses to re-send a campaign that's already Sent.
 *
 * Creates one CustomerCampaignLog per recipient with a unique tracking
 * token, which the mailable embeds as a 1×1 open-tracking pixel. The
 * /track/email-open/{token}.gif endpoint stamps opened_at when the
 * customer's mail client loads the image.
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

            $log = CustomerCampaignLog::query()->create([
                'customer_campaign_id' => $campaign->id,
                'customer_email' => $customer->email,
                'tracking_token' => $this->mintToken(),
            ]);

            Mail::to($customer->email)->queue(new CustomerCampaignMail($campaign, $log->tracking_token));
            $sent++;
        }

        $campaign->forceFill([
            'status' => CustomerCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $sent,
        ])->save();

        return $sent;
    }

    private function mintToken(): string
    {
        do {
            $token = (string) Str::ulid();
        } while (CustomerCampaignLog::query()->where('tracking_token', $token)->exists());

        return $token;
    }
}
