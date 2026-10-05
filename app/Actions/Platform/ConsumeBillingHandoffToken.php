<?php

namespace App\Actions\Platform;

use App\Exceptions\Platform\BillingHandoffRefusedException;
use App\Models\Platform\BillingHandoffToken;
use App\Models\Staff\User;

class ConsumeBillingHandoffToken
{
    /**
     * Redeem a handoff token and return the bakery owner it was issued for.
     *
     * @throws BillingHandoffRefusedException
     */
    public function __invoke(string $token, ?string $consumerIp = null): User
    {
        $hash = hash('sha256', $token);

        // Claim the token with one conditional UPDATE so two simultaneous
        // requests cannot both redeem it. It is marked consumed, not deleted,
        // so the table keeps a record of every redemption.
        $claimed = BillingHandoffToken::query()
            ->where('token_hash', $hash)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->update([
                'consumed_at' => now(),
                'consumer_ip' => $consumerIp,
            ]);

        $record = BillingHandoffToken::query()
            ->with('tenant')
            ->where('token_hash', $hash)
            ->first();

        if ($claimed !== 1 || $record === null) {
            throw new BillingHandoffRefusedException($record?->tenant);
        }

        // The bakery's owner can change after the token is issued, so the
        // token only signs in the owner the bakery still has.
        if ($record->tenant?->user_id !== $record->user_id) {
            throw new BillingHandoffRefusedException($record->tenant);
        }

        return User::query()->findOr($record->user_id, fn () => throw new BillingHandoffRefusedException($record->tenant));
    }
}
