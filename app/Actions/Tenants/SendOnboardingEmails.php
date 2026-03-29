<?php

namespace App\Actions\Tenants;

use App\Enums\SubscriptionTier;
use App\Mail\NewSubscriberNotificationMail;
use App\Mail\WelcomeBakerMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOnboardingEmails
{
    public function __invoke(User $user, string $storeName, string $subdomain, string $adminUrl): void
    {
        try {
            Mail::to($user->email)->send(new WelcomeBakerMail(
                bakerName: $user->name,
                storeName: $storeName,
                adminUrl: $adminUrl,
                plan: SubscriptionTier::Starter->value,
                trialEndsAt: now()->addDays(config('kneadit.trial_days', 30))->format('F j, Y'),
            ));

            Mail::to(config('mail.platform_notify'))->send(new NewSubscriberNotificationMail(
                bakerName: $user->name,
                bakerEmail: $user->email,
                storeName: $storeName,
                subdomain: $subdomain,
                plan: SubscriptionTier::Starter->value,
            ));
        } catch (\Exception $e) {
            Log::warning('Signup emails failed', ['error' => $e->getMessage()]);
        }
    }
}
