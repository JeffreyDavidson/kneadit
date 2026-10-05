<?php

namespace App\Actions\Platform;

use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Events\Marketing\CampaignEmailQueued;
use App\Exceptions\Platform\PlatformCampaignContextException;
use App\Models\Engagement\EmailCampaign;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Sends a platform email campaign to the owner address of each bakery in the
 * campaign's segment. Bakery customers are never recipients; bakeries email
 * their own customers with customer campaigns.
 */
class SendEmailCampaign
{
    public function __invoke(EmailCampaign $campaign): void
    {
        if (tenancy()->initialized) {
            throw PlatformCampaignContextException::insideTenant();
        }

        $campaign->update(['status' => EmailCampaignStatus::Sending]);

        foreach ($this->ownerEmails($campaign->target_segment) as $email => $tenantId) {
            // The log row is the once-per-recipient record: it is written before the
            // mail goes out, and the unique index on (campaign, email) refuses a
            // second row, so a send that died partway and is retried skips every
            // owner already logged. A crash between the row and the mail loses
            // that one email, which is preferred over emailing an owner twice.
            try {
                $campaign->logs()->create([
                    'tenant_id' => $tenantId,
                    'email' => $email,
                    'sent_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                continue;
            }

            event(new CampaignEmailQueued($email, $campaign->subject, $campaign->body));
        }

        $campaign->update([
            'status' => EmailCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $campaign->logs()->count(),
        ]);
    }

    /**
     * Owner address (lowercased and trimmed, so case variants count as one) mapped to a tenant id.
     *
     * @return Collection<lowercase-string, string>
     */
    private function ownerEmails(EmailCampaignSegment $segment): Collection
    {
        return $this->segmentTenants($segment)
            ->whereNotNull('email')
            ->get(['id', 'email'])
            ->mapWithKeys(fn (Tenant $tenant): array => [Str::lower(trim($tenant->email)) => $tenant->id])
            ->forget('');
    }

    /** @return Builder<Tenant> */
    private function segmentTenants(EmailCampaignSegment $segment): Builder
    {
        $query = Tenant::query();

        return match ($segment) {
            EmailCampaignSegment::All => $query->where('is_active', true),
            EmailCampaignSegment::Starter, EmailCampaignSegment::Growth, EmailCampaignSegment::Pro => $query->where('is_active', true)->where('plan', SubscriptionTier::from($segment->value)),
            EmailCampaignSegment::Trial => $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now()),
            EmailCampaignSegment::Inactive => $query->where('is_active', false),
        };
    }
}
