<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    /**
     * Central admin: generate a one-time token and redirect to tenant domain.
     */
    public function __invoke(Request $request, Tenant $tenant)
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
}
