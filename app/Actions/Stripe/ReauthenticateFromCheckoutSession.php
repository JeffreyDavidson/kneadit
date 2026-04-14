<?php

namespace App\Actions\Stripe;

use App\Models\Staff\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class ReauthenticateFromCheckoutSession
{
    public function __invoke(string $sessionId): void
    {
        $checkoutSession = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if ($checkoutSession->status !== 'complete'
            || ! $checkoutSession->customer
            || $checkoutSession->created < now()->subMinutes(30)->getTimestamp()
        ) {
            return;
        }

        $user = User::query()->where('stripe_id', $checkoutSession->customer)->first();

        if ($user) {
            Auth::login($user);
        }
    }
}
