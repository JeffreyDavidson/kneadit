<?php

namespace App\Listeners\Platform;

use App\Events\Platform\ScheduledCheckinDue;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\ScheduledCheckinMail;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Contracts\Mail\Mailable;

class SendScheduledCheckinEmailListener extends SendEmailListener
{
    public function __construct(private readonly TenantUrlGenerator $tenantUrls) {}

    protected function getRecipient(object $event): ?string
    {
        /** @var ScheduledCheckinDue $event */
        return $event->tenantEmail;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var ScheduledCheckinDue $event */
        $adminUrl = $event->adminUrl;
        $helpUrl = $event->helpUrl;

        // Keep queued events created before generated URLs were added deploy-safe.
        if (($adminUrl === null || $helpUrl === null) && $event->tenantId !== null) {
            $tenant = new Tenant(['id' => $event->tenantId]);
            $adminUrl ??= $this->tenantUrls->admin($tenant);
            $helpUrl ??= $this->tenantUrls->helpCenter($tenant);
        }

        return new ScheduledCheckinMail(
            body: $event->body,
            emailSubject: $event->subject,
            bakerName: $event->bakerName,
            adminUrl: $adminUrl,
            helpUrl: $helpUrl,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ScheduledCheckinDue $event */
        return ['email' => $event->tenantEmail];
    }
}
