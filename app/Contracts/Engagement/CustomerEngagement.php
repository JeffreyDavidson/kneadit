<?php

namespace App\Contracts\Engagement;

use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;

interface CustomerEngagement
{
    /**
     * Check whether this engagement type is enabled for the tenant.
     */
    public function isEnabled(TenantSettings $settings): bool;

    /**
     * Query and return eligible recipients.
     *
     * @return Collection<int, EngagementRecipient>
     */
    public function findRecipients(TenantSettings $settings): Collection;

    /**
     * Process a single recipient: perform side effects and dispatch the domain event.
     */
    public function dispatchForRecipient(EngagementRecipient $recipient, TenantSettings $settings): void;
}
