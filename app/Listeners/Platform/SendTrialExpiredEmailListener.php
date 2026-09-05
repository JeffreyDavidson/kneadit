<?php

namespace App\Listeners\Platform;

use App\Events\Platform\TrialExpired;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\TrialExpiredMail;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Contracts\Mail\Mailable;

class SendTrialExpiredEmailListener extends SendEmailListener
{
    public function __construct(private readonly TenantUrlGenerator $tenantUrls) {}

    protected function getRecipient(object $event): ?string
    {
        /** @var TrialExpired $event */
        return $event->user->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var TrialExpired $event */
        $adminUrl = $event->adminUrl;

        // Keep queued events created before adminUrl was added deploy-safe.
        if ($adminUrl === null) {
            $adminUrl = $this->tenantUrls->admin(new Tenant(['id' => $event->tenantId]));
        }

        return new TrialExpiredMail(
            user: $event->user,
            adminUrl: $adminUrl,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var TrialExpired $event */
        return ['email' => $event->user->email];
    }
}
