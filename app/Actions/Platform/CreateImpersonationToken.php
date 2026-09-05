<?php

namespace App\Actions\Platform;

use App\Models\Platform\ImpersonationToken;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Str;

class CreateImpersonationToken
{
    public function __construct(
        private TenantUrlGenerator $tenantUrlGenerator,
    ) {}

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

        return $this->tenantUrlGenerator->impersonation($tenant, $token);
    }
}
