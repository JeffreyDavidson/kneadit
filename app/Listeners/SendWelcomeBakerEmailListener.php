<?php

namespace App\Listeners;

use App\Enums\SubscriptionTier;
use App\Events\TenantOnboarded;
use App\Mail\WelcomeBakerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeBakerEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(TenantOnboarded $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeBakerMail(
            bakerName: $event->user->name,
            storeName: $event->tenant->store_name,
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
