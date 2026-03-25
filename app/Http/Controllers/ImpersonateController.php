<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    /**
     * Central admin: generate a one-time token and redirect to tenant domain.
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless(
            $request->user() && $request->user()->role === UserRole::PlatformAdmin,
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
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'https';
        $host = "{$domain}.{$appHost}";

        return redirect()->to("{$scheme}://{$host}/impersonate/{$token}");
    }
}
