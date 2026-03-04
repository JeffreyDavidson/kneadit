<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function show()
    {
        return view('onboarding');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|alpha_dash|unique:domains,domain',
        ]);

        $subdomain = Str::lower($validated['subdomain']);

        // Create the tenant
        $tenant = Tenant::create([
            'id' => $subdomain,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'plan' => 'starter',
            'trial_ends_at' => now()->addDays(config('saas.trial_days', 30)),
            'store_name' => $validated['store_name'],
            'is_active' => true,
        ]);

        // Add the subdomain
        $tenant->domains()->create([
            'domain' => $subdomain . '.' . config('tenancy.central_domains.0', 'getkneadit.app'),
        ]);

        // Create the tenant's database and run migrations
        $tenant->run(function () use ($request) {
            // Create the owner user in the tenant database
            \App\Models\User::create([
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'password' => $request->user()->password, // Already hashed
                'email_verified_at' => now(),
            ]);

            // Seed default settings
            \App\Models\Setting::set('store_name', $request->input('store_name'));
            \App\Models\Setting::set('store_email', $request->user()->email);
        });

        // Redirect to their new admin panel
        $tenantUrl = 'http://' . $subdomain . '.' . config('tenancy.central_domains.0', 'getkneadit.app') . '/admin';

        return redirect()->away($tenantUrl);
    }
}
