<?php

namespace App\Actions\Platform;

use App\Models\Platform\ImpersonationToken;
use App\Models\Platform\Tenant;
use Illuminate\Support\Str;

class CreateImpersonationToken
{
    public function __invoke(Tenant $tenant, ?int $createdByUserId = null): string
    {
        $token = Str::random(64);

        ImpersonationToken::query()->create([
            'token' => hash('sha256', $token),
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $createdByUserId ?? auth()->id(),
            'expires_at' => now()->addSeconds(60),
            'created_at' => now(),
        ]);

        // Use the tenant id as the subdomain (not the domains relation, since
        // tenants store both the FQDN AND the bare subdomain — concatenating
        // either with $appHost can produce a doubled host like
        // "{id}.kneadit.test.kneadit.test").
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'https';

        return "{$scheme}://{$tenant->id}.{$appHost}/impersonate/{$token}";
    }
}
