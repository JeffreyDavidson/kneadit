<?php

namespace App\Actions\Platform;

use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Events\Marketing\CampaignEmailQueued;
use App\Models\Customers\Customer;
use App\Models\Engagement\EmailCampaign;
use App\Models\Platform\Tenant;
use App\Services\Customers\MarketingUnsubscribeLinks;
use App\Services\Tenants\TenancyManager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class SendEmailCampaign
{
    public function __construct(
        private readonly TenancyManager $tenancyManager,
        private readonly MarketingUnsubscribeLinks $unsubscribeLinks,
    ) {}

    public function __invoke(EmailCampaign $campaign): void
    {
        $campaign->update(['status' => EmailCampaignStatus::Sending]);

        $recipients = tenancy()->initialized
            ? $this->currentTenantRecipients()
            : $this->recipientsBySegment($campaign->target_segment);

        foreach ($recipients as $email => $unsubscribeUrl) {
            event(new CampaignEmailQueued((string) $email, $campaign->subject, $campaign->body, $unsubscribeUrl));
        }

        $campaign->update([
            'status' => EmailCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $recipients->count(),
        ]);
    }

    /**
     * Each current-tenant customer who has not unsubscribed from marketing,
     * keyed by email with their personal unsubscribe link as the value.
     *
     * @return Collection<string, string>
     */
    private function currentTenantRecipients(): Collection
    {
        return Customer::query()
            ->subscribedToMarketing()
            ->whereNotNull('email')
            ->get()
            ->mapWithKeys(fn (Customer $customer): array => [$customer->email => $this->unsubscribeLinks->unsubscribe($customer)]);
    }

    /** @return Collection<string, string> */
    private function recipientsBySegment(EmailCampaignSegment $segment): Collection
    {
        $recipients = new Collection;

        foreach ($this->filteredTenants($segment) as $tenant) {
            $this->tenancyManager->withinTenant($tenant, function () use (&$recipients): void {
                $recipients = $recipients->union($this->currentTenantRecipients());
            });
        }

        return $recipients;
    }

    /** @return EloquentCollection<int, Tenant> */
    private function filteredTenants(EmailCampaignSegment $segment): EloquentCollection
    {
        $query = Tenant::query();

        match ($segment) {
            EmailCampaignSegment::All => $query->where('is_active', true),
            EmailCampaignSegment::Starter, EmailCampaignSegment::Growth, EmailCampaignSegment::Pro => $query->where('is_active', true)->where('plan', SubscriptionTier::from($segment->value)),
            EmailCampaignSegment::Trial => $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now()),
            EmailCampaignSegment::Inactive => $query->where('is_active', false),
        };

        /** @var EloquentCollection<int, Tenant> $tenants */
        $tenants = new EloquentCollection($query->get()->all());

        return $tenants;
    }
}
