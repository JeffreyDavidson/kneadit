<?php

namespace App\Listeners\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Services\Tenants\TenantUrlGenerator;
use Filament\Facades\Filament;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Config;

class NotifyPlatformOfNewTenantListener extends SendEmailListener
{
    public function __construct(private readonly TenantUrlGenerator $tenantUrls) {}

    protected function getRecipient(object $event): ?string
    {
        $recipient = Config::get('mail.platform_notify');

        return is_string($recipient) ? $recipient : null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var TenantOnboarded $event */
        $centralAdminUrl = Filament::getPanel('central')->getUrl();

        if ($centralAdminUrl === null) {
            throw new \UnexpectedValueException('The central admin panel must have a URL.');
        }

        return new NewSubscriberNotificationMail(
            bakerName: $event->user->name,
            bakerEmail: $event->user->email,
            storeName: $event->tenant->store_name ?? $event->tenant->name,
            storefrontHost: $this->tenantUrls->storefrontHost($event->tenant),
            plan: SubscriptionTier::Starter->value,
            centralAdminUrl: $centralAdminUrl,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var TenantOnboarded $event */
        return ['tenant' => $event->tenant->id];
    }
}
