<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    /**
     * Central admin: generate a one-time token and redirect to tenant domain.
     */
    public function login(Request $request, Tenant $tenant)
    {
        abort_unless(
            $request->user() && $request->user()->role === 'platform_admin',
            403,
            'Unauthorized.'
        );

        $token = Str::random(64);

        // Store token in central DB (shared across domains)
        DB::connection('central')->table('impersonation_tokens')->insert([
            'token' => hash('sha256', $token),
            'tenant_id' => $tenant->id,
            'expires_at' => now()->addSeconds(60),
            'created_at' => now(),
        ]);

        $domain = $tenant->domains()->first()?->domain ?? $tenant->id;
        $host = app()->environment('local')
            ? $domain.'.kneadit.test'
            : $domain.'.getkneadit.app';
        $scheme = app()->environment('local') ? 'http' : 'https';

        return redirect()->to("{$scheme}://{$host}/impersonate/{$token}");
    }

    /**
     * Tenant side: consume the token and log in as the first user.
     */
    public function consume(Request $request, string $token)
    {
        $hashed = hash('sha256', $token);
        $record = DB::connection('central')->table('impersonation_tokens')
            ->where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            abort(403, 'Invalid or expired impersonation token.');
        }

        // Delete the token (one-time use)
        DB::connection('central')->table('impersonation_tokens')
            ->where('token', $hashed)
            ->delete();

        $user = User::first();

        if (! $user) {
            abort(404, 'No users found for this tenant.');
        }

        Auth::login($user);

        return redirect()->to('/admin');
    }
}
