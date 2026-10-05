<?php

namespace App\Actions\Marketing;

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\CustomerCampaignLog;
use App\Services\Customers\ResolveCampaignRecipients;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Sends a customer campaign to all recipients matching its target segment.
 * Idempotent guard: refuses to re-send a campaign that's already Sent, and
 * emails each recipient at most once even when a partial send is retried.
 *
 * Creates one CustomerCampaignLog per recipient with a unique tracking
 * token, which the mailable embeds as a 1×1 open-tracking pixel. The
 * /track/email-open/{token}.gif endpoint stamps opened_at when the
 * customer's mail client loads the image.
 */
class SendCustomerCampaign
{
    public function __construct(
        private readonly ResolveCampaignRecipients $resolveRecipients,
    ) {}

    public function __invoke(CustomerCampaign $campaign): int
    {
        if ($campaign->status === CustomerCampaignStatus::Sent) {
            return 0;
        }

        $claimed = CustomerCampaign::query()
            ->whereKey($campaign->getKey())
            ->whereIn('status', [CustomerCampaignStatus::Draft, CustomerCampaignStatus::Scheduled])
            ->update([
                'status' => CustomerCampaignStatus::Sending,
                'updated_at' => now(),
            ]);

        if ($claimed !== 1) {
            return 0;
        }

        $campaign->refresh();

        $recipients = ($this->resolveRecipients)($campaign->target_segment);

        $sent = 0;
        foreach ($recipients as $customer) {
            if (! $customer->email) {
                continue;
            }

            // The log row is the once-per-recipient record: it is written before the
            // mail is queued, and the unique index on (campaign, email) refuses a
            // second row. So a send that died partway and is retried skips everyone
            // already logged. A crash between the row and the queue push loses that
            // one email, which is preferred over emailing a customer twice.
            try {
                $log = $campaign->logs()->create([
                    'customer_email' => $customer->email,
                    'tracking_token' => $this->mintToken(),
                ]);
            } catch (UniqueConstraintViolationException) {
                continue;
            }

            Mail::to($customer->email)->queue(new CustomerCampaignMail($campaign, $customer, $log->tracking_token));
            $sent++;
        }

        $campaign->forceFill([
            'status' => CustomerCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $campaign->logs()->count(),
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
