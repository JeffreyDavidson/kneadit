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
use Illuminate\Support\Collection;

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

        $emails = $this->ownerEmails($campaign->target_segment);

        foreach ($emails as $email) {
            event(new CampaignEmailQueued($email, $campaign->subject, $campaign->body));
        }

        $campaign->update([
            'status' => EmailCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $emails->count(),
        ]);
    }

    /** @return Collection<int, non-empty-string> */
    private function ownerEmails(EmailCampaignSegment $segment): Collection
    {
        return $this->segmentTenants($segment)
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn (mixed $email): bool => is_string($email) && $email !== '')
            ->unique()
            ->values();
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
