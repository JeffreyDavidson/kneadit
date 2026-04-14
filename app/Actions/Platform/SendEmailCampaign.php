<?php

namespace App\Actions\Platform;

use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Events\Marketing\CampaignEmailQueued;
use App\Models\Customers\Customer;
use App\Models\Engagement\EmailCampaign;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class SendEmailCampaign
{
    public function __construct(
        private TenancyManager $tenancyManager,
    ) {}

    public function __invoke(EmailCampaign $campaign): void
    {
        $campaign->update(['status' => EmailCampaignStatus::Sending]);

        $emails = tenancy()->initialized
            ? $this->currentTenantEmails()
            : $this->emailsBySegment($campaign->target_segment);

        foreach ($emails as $email) {
            CampaignEmailQueued::dispatch($email, $campaign->subject, $campaign->body);
        }

        $campaign->update([
            'status' => EmailCampaignStatus::Sent,
            'sent_at' => now(),
            'recipient_count' => $emails->count(),
        ]);
    }

    /** @return Collection<int, string> */
    private function currentTenantEmails(): Collection
    {
        return Customer::query()
            ->whereNotNull('email')
            ->distinct()
            ->pluck('email');
    }

    /** @return Collection<int, string> */
    private function emailsBySegment(EmailCampaignSegment $segment): Collection
    {
        $emails = collect();

        foreach ($this->filteredTenants($segment) as $tenant) {
            $this->tenancyManager->withinTenant($tenant, function () use (&$emails) {
                $emails = $emails->merge($this->currentTenantEmails());
            });
        }

        return $emails->unique()->values();
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

        return $query->get();
    }
}
