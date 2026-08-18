<?php

namespace App\Listeners\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\WelcomeBakerMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Config;

class SendWelcomeBakerEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var TenantOnboarded $event */
        return $event->user->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var TenantOnboarded $event */
        return new WelcomeBakerMail(
            bakerName: $event->user->name,
            storeName: $event->tenant->store_name ?? $event->tenant->name,
            adminUrl: $event->adminUrl,
            plan: SubscriptionTier::Starter->value,
            trialEndsAt: now()->addDays(Config::integer('kneadit.trial_days', 30))->format('F j, Y'),
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var TenantOnboarded $event */
        return ['tenant' => $event->tenant->id];
    }
}
