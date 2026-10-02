<?php

namespace App\Actions\Platform;

use App\Models\Platform\ImpersonationToken;
use App\Models\Staff\User;

class ConsumeImpersonationToken
{
    public function __invoke(string $token, ?string $tenantId, ?string $consumerIp = null): User
    {
        abort_if($tenantId === null, 403, 'Invalid or expired impersonation token.');

        // Claim the token with one conditional UPDATE so two simultaneous
        // requests cannot both consume it. The token is marked consumed (not
        // deleted) so the audit log retains a record of every successful
        // impersonation, and it only matches the bakery it was issued for.
        $claimed = ImpersonationToken::query()
            ->where('token', hash('sha256', $token))
            ->where('tenant_id', $tenantId)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->update([
                'consumed_at' => now(),
                'consumer_ip' => $consumerIp,
            ]);

        abort_unless($claimed === 1, 403, 'Invalid or expired impersonation token.');

        $user = User::query()->owners()->first()
            ?? User::query()->first();

        abort_unless((bool) $user, 404, 'No users found for this tenant.');

        return $user;
    }
}
