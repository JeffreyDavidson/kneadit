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

        // Keep queued events created before adminUrl was added deploy-safe.
        if ($adminUrl === null && $event->tenantId !== null) {
            $adminUrl = $this->tenantUrls->admin(new Tenant(['id' => $event->tenantId]));
        }

        return new ScheduledCheckinMail(
            body: $event->body,
            emailSubject: $event->subject,
            bakerName: $event->bakerName,
            adminUrl: $adminUrl,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ScheduledCheckinDue $event */
        return ['email' => $event->tenantEmail];
    }
}
