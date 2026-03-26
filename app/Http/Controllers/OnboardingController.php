<?php

namespace App\Http\Controllers;

use App\Actions\CompleteReferral;
use App\Actions\Tenants\CreateTenant;
use App\Enums\SubscriptionTier;
use App\Http\Requests\StoreOnboardingRequest;
use App\Mail\NewSubscriberNotification;
use App\Mail\WelcomeBaker;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function show(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('onboarding', [
            'bakeryName' => session('bakery_name', ''),
        ]);
    }

    public function store(
        StoreOnboardingRequest $request,
        CreateTenant $createTenant,
        CompleteReferral $completeReferral,
    ): RedirectResponse {
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validated();

        $subdomain = Str::lower($validated['subdomain']);
        $useKneadItStorefront = $validated['storefront_choice'] === 'kneadit';

        $tenant = $createTenant(
            user: $user,
            storeName: $validated['store_name'],
            subdomain: $subdomain,
            useKneadItStorefront: $useKneadItStorefront,
            externalWebsite: $validated['external_website'] ?? null,
        );

        // Complete referral if one exists
        $referralCode = $request->session()->get('referral_code') ?? $request->cookie('referral_code');
        if ($referralCode) {
            $completeReferral(
                referralCode: $referralCode,
                tenantId: (string) $tenant->id,
                email: $user->email,
            );
        }

        // Send welcome email to the baker
        $scheme = $request->secure() ? 'https' : 'http';
        $host = $request->getHost();
        $adminUrl = "{$scheme}://{$subdomain}.{$host}/admin";

        try {
            Mail::to($user->email)->send(new WelcomeBaker(
                bakerName: $user->name,
                storeName: $validated['store_name'],
                adminUrl: $adminUrl,
                plan: SubscriptionTier::Starter->value,
                trialEndsAt: now()->addDays(config('saas.trial_days', 30))->format('F j, Y'),
            ));

            Mail::to(config('mail.platform_notify'))->send(new NewSubscriberNotification(
                bakerName: $user->name,
                bakerEmail: $user->email,
                storeName: $validated['store_name'],
                subdomain: $subdomain,
                plan: SubscriptionTier::Starter->value,
            ));
        } catch (\Exception $e) {
            Log::warning('Signup emails failed', ['error' => $e->getMessage()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($adminUrl);
    }
}
