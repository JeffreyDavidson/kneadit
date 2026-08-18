<?php

namespace App\Listeners\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\NewSubscriberNotificationMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Config;

class NotifyPlatformOfNewTenantListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        $recipient = Config::get('mail.platform_notify');

        return is_string($recipient) ? $recipient : null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var TenantOnboarded $event */
        return new NewSubscriberNotificationMail(
            bakerName: $event->user->name,
            bakerEmail: $event->user->email,
            storeName: $event->tenant->store_name ?? $event->tenant->name,
            subdomain: (string) $event->tenant->id,
            plan: SubscriptionTier::Starter->value,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var TenantOnboarded $event */
        return ['tenant' => $event->tenant->id];
    }
}
