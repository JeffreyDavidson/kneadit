<?php

namespace App\Http\Controllers;

use App\Mail\NewSubscriberNotification;
use App\Mail\WelcomeBaker;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function show()
    {
        return view('onboarding', [
            'bakeryName' => session('bakery_name', ''),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'store_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|alpha_dash|not_in:www,mail,admin,api,app,blog,cdn,dev,ftp,help,imap,login,mx,ns,pop,smtp,staging,status,support,test,webmail|unique:domains,domain',
            'storefront_choice' => 'required|in:kneadit,own',
        ];

        // Only require external_website when they chose "own"
        if ($request->input('storefront_choice') === 'own') {
            $rules['external_website'] = 'required|url|max:255';
        }

        $validated = $request->validate($rules);

        $subdomain = Str::lower($validated['subdomain']);
        $useKneadItStorefront = $validated['storefront_choice'] === 'kneadit';

        // Create the tenant, domain, and seed initial data
        $tenant = DB::transaction(function () use ($request, $validated, $subdomain, $useKneadItStorefront) {
            $tenant = Tenant::create([
                'id' => $subdomain,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'plan' => 'starter',
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
        $tenant->run(function () use ($request, $validated, $useKneadItStorefront) {
            // Create the owner user in the tenant database
            // Use query builder to avoid the 'hashed' cast double-hashing the password
            DB::table('users')->insert([
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'password' => $request->user()->password,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Seed default settings
            \App\Models\Setting::set('store_name', $validated['store_name']);
            \App\Models\Setting::set('store_email', $request->user()->email);
            \App\Models\Setting::set('storefront_enabled', $useKneadItStorefront ? '1' : '0');

            if (! $useKneadItStorefront && isset($validated['external_website'])) {
                \App\Models\Setting::set('external_website', $validated['external_website']);
            }
        });

        // Complete referral if one exists
        $referralCode = $request->session()->get('referral_code') ?? $request->cookie('referral_code');
        if ($referralCode) {
            $referral = \App\Models\Referral::where('referral_code', $referralCode)
                ->where('status', 'pending')
                ->whereNull('referred_tenant_id')
                ->first();

            if ($referral) {
                $referral->update([
                    'referred_tenant_id' => $tenant->id,
                    'referred_email' => $request->user()->email,
                    'status' => 'completed',
                ]);
            }
        }

        // Send welcome email to the baker
        $scheme = $request->secure() ? 'https' : 'http';
        $host = $request->getHost();
        $adminUrl = "{$scheme}://{$subdomain}.{$host}/admin";

        try {
            Mail::to($request->user()->email)->send(new WelcomeBaker(
                bakerName: $request->user()->name,
                storeName: $validated['store_name'],
                adminUrl: $adminUrl,
                plan: 'starter',
                trialEndsAt: now()->addDays(config('saas.trial_days', 30))->format('F j, Y'),
            ));

            // Notify platform owner
            Mail::to(config('mail.platform_notify'))->send(new NewSubscriberNotification(
                bakerName: $request->user()->name,
                bakerEmail: $request->user()->email,
                storeName: $validated['store_name'],
                subdomain: $subdomain,
                plan: 'starter',
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Signup emails failed', ['error' => $e->getMessage()]);
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
