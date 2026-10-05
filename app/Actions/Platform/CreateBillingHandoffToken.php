<?php

namespace App\Actions\Platform;

use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Str;

class CreateBillingHandoffToken
{
    public function __construct(
        private readonly TenantUrlGenerator $tenantUrlGenerator,
    ) {}

    /**
     * Issue a two-minute, single-use pass for the bakery's central owner and
     * return the central URL that redeems it. Only the hash is stored.
     */
    public function __invoke(Tenant $tenant): string
    {
        // Fail closed: a bakery with no linked owner has nobody to sign in. Never match by email.
        abort_if($tenant->user_id === null, 403, 'This bakery has no linked owner account.');

        $token = Str::random(64);

        BillingHandoffToken::query()->create([
            'token_hash' => hash('sha256', $token),
            'tenant_id' => $tenant->id,
            'user_id' => $tenant->user_id,
            'expires_at' => now()->addMinutes(2),
            'created_at' => now(),
        ]);

        return $this->tenantUrlGenerator->billingHandoff($token);
    }
}
