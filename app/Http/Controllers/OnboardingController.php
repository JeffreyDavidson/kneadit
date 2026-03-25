<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Enums\SubscriptionTier;
use App\Http\Requests\StoreOnboardingRequest;
use App\Mail\NewSubscriberNotification;
use App\Mail\WelcomeBaker;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function store(StoreOnboardingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validated();

        $subdomain = Str::lower($validated['subdomain']);
        $useKneadItStorefront = $validated['storefront_choice'] === 'kneadit';

        // Create the tenant, domain, and seed initial data
        $tenant = DB::transaction(function () use ($validated, $subdomain, $useKneadItStorefront, $user) {
            $tenant = Tenant::query()->create([
                'id' => $subdomain,
                'name' => $user->name,
                'email' => $user->email,
                'plan' => SubscriptionTier::Starter->value,
                'trial_ends_at' => now()->addDays(config('saas.trial_days', 30)),
                'store_name' => $validated['store_name'],
                'storefront_enabled' => $useKneadItStorefront,
                'external_website' => $useKneadItStorefront ? null : ($validated['external_website'] ?? null),
                'is_active' => true,
            ]);

            $tenant->domains()->create([
                'domain' => $subdomain,
            ]);

            return $tenant;
        });

        // Create the tenant's database and run migrations
        $tenant->run(function () use ($validated, $useKneadItStorefront, $user) {
            // Create the owner user in the tenant database
            // Use query builder to avoid the 'hashed' cast double-hashing the password
            DB::table('users')->insert([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Seed default settings
            Setting::set('store_name', $validated['store_name']);
            Setting::set('store_email', $user->email);
            Setting::set('storefront_enabled', $useKneadItStorefront ? '1' : '0');

            if (! $useKneadItStorefront && isset($validated['external_website'])) {
                Setting::set('external_website', $validated['external_website']);
            }
        });

        // Complete referral if one exists
        $referralCode = $request->session()->get('referral_code') ?? $request->cookie('referral_code');
        if ($referralCode) {
            $referral = Referral::query()->where('referral_code', $referralCode)
                ->where('status', ReferralStatus::Pending)
                ->whereNull('referred_tenant_id')
                ->first();

            if ($referral) {
                $referral->update([
                    'referred_tenant_id' => $tenant->id,
                    'referred_email' => $user->email,
                    'status' => ReferralStatus::Completed,
                ]);
            }
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

            // Notify platform owner
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

        // Clear central session — they'll log in fresh on their tenant subdomain
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to their new admin panel
        $tenantUrl = $adminUrl;

        return redirect()->away($tenantUrl);
    }
}
