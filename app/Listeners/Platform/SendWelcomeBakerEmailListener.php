<?php

namespace App\Listeners\Platform;

use App\Enums\Platform\SubscriptionTier;
use App\Events\Platform\TenantOnboarded;
use App\Listeners\QueuedListener;
use App\Mail\Platform\WelcomeBakerMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeBakerEmailListener extends QueuedListener
{
    public function handle(TenantOnboarded $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeBakerMail(
            bakerName: $event->user->name,
            storeName: $event->tenant->store_name ?? $event->tenant->name,
            adminUrl: $event->adminUrl,
            plan: SubscriptionTier::Starter->value,
            trialEndsAt: now()->addDays(config('kneadit.trial_days', 30))->format('F j, Y'),
        ));
    }

    public function failed(TenantOnboarded $event, \Throwable $exception): void
    {
        Log::warning('Welcome baker email failed', [
            'tenant' => $event->tenant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
