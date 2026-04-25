<?php

namespace App\Actions\Platform;

use App\Models\Platform\ImpersonationToken;
use App\Models\Staff\User;

class ConsumeImpersonationToken
{
    public function __invoke(string $token, ?string $consumerIp = null): User
    {
        $record = ImpersonationToken::query()
            ->where('token', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();

        abort_unless((bool) $record, 403, 'Invalid or expired impersonation token.');

        // Mark consumed (don't delete) so the audit log retains a record of
        // every successful impersonation.
        $record->update([
            'consumed_at' => now(),
            'consumer_ip' => $consumerIp,
        ]);

        $user = User::query()->owners()->first()
            ?? User::query()->first();

        abort_unless((bool) $user, 404, 'No users found for this tenant.');

        return $user;
    }
}
