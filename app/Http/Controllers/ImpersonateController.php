<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function login(Request $request, Tenant $tenant)
    {
        try {
            tenancy()->initialize($tenant);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Could not connect to tenant database: ' . $e->getMessage());
        }

        $user = \App\Models\User::first();

        if (! $user) {
            tenancy()->end();
            return redirect()->back()->with('error', 'No users found for this tenant.');
        }

        Auth::login($user);

        $domain = $tenant->domains()->first()?->domain ?? $tenant->id;
        $host = app()->environment('local')
            ? $domain . '.kneadit.test'
            : $domain . '.getkneadit.app';
        $scheme = app()->environment('local') ? 'http' : 'https';

        return redirect()->to("{$scheme}://{$host}/admin");
    }
}
