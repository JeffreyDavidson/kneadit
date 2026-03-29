<?php

namespace App\Http\Controllers;

use App\Actions\Tenants\CompleteReferral;
use App\Actions\Tenants\CreateTenant;
use App\Actions\Tenants\SendOnboardingEmails;
use App\Http\Requests\StoreOnboardingRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
        SendOnboardingEmails $sendEmails,
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

        $scheme = $request->secure() ? 'https' : 'http';
        $host = $request->getHost();
        $adminUrl = "{$scheme}://{$subdomain}.{$host}/admin";

        $sendEmails($user, $validated['store_name'], $subdomain, $adminUrl);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($adminUrl);
    }
}
