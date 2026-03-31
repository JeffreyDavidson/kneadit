<?php

namespace App\Listeners;

use App\Enums\SubscriptionTier;
use App\Events\TenantOnboarded;
use App\Mail\NewSubscriberNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyPlatformOfNewTenantListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(TenantOnboarded $event): void
    {
        Mail::to(config('mail.platform_notify'))->send(new NewSubscriberNotificationMail(
            bakerName: $event->user->name,
            bakerEmail: $event->user->email,
            storeName: $event->tenant->store_name,
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
