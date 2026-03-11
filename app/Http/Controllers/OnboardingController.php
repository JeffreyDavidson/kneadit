<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
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
            'subdomain' => 'required|string|max:63|alpha_dash|unique:domains,domain',
            'storefront_choice' => 'required|in:kneadit,own',
        ];

        // Only require external_website when they chose "own"
        if ($request->input('storefront_choice') === 'own') {
            $rules['external_website'] = 'required|url|max:255';
        }

        $validated = $request->validate($rules);

        $subdomain = Str::lower($validated['subdomain']);
        $useKneadItStorefront = $validated['storefront_choice'] === 'kneadit';

        // Create the tenant
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

        // Add the subdomain
        $tenant->domains()->create([
            'domain' => $subdomain.'.'.config('tenancy.central_domains.0', 'getkneadit.app'),
        ]);

        // Create the tenant's database and run migrations
        $tenant->run(function () use ($request, $validated, $useKneadItStorefront) {
            // Create the owner user in the tenant database
            \App\Models\User::create([
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'password' => $request->user()->password,
                'email_verified_at' => now(),
            ]);

            // Seed default settings
            \App\Models\Setting::set('store_name', $validated['store_name']);
            \App\Models\Setting::set('store_email', $request->user()->email);
            \App\Models\Setting::set('storefront_enabled', $useKneadItStorefront ? '1' : '0');

            if (! $useKneadItStorefront && isset($validated['external_website'])) {
                \App\Models\Setting::set('external_website', $validated['external_website']);
            }
        });

        // Redirect to their new admin panel
        $tenantUrl = 'http://'.$subdomain.'.'.config('tenancy.central_domains.0', 'getkneadit.app').'/admin';

        return redirect()->away($tenantUrl);
    }
}
