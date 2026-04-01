<?php

namespace App\Listeners\Platform;

use App\Enums\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Listeners\QueuedListener;
use App\Mail\Platform\NewSubscriberNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyPlatformOfNewTenantListener extends QueuedListener
{
    public function handle(TenantOnboarded $event): void
    {
        Mail::to(config('mail.platform_notify'))->send(new NewSubscriberNotificationMail(
            bakerName: $event->user->name,
            bakerEmail: $event->user->email,
            storeName: $event->tenant->store_name ?? $event->tenant->name,
            subdomain: (string) $event->tenant->id,
            plan: SubscriptionTier::Starter->value,
        ));
    }

    public function failed(TenantOnboarded $event, \Throwable $exception): void
    {
        Log::warning('Platform new tenant notification failed', [
            'tenant' => $event->tenant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
