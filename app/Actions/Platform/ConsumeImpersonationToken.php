<?php

namespace App\Actions\Platform;

use App\Enums\UserRole;
use App\Models\ImpersonationToken;
use App\Models\User;

class ConsumeImpersonationToken
{
    public function __invoke(string $token): User
    {
        $record = ImpersonationToken::query()
            ->where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        abort_unless((bool) $record, 403, 'Invalid or expired impersonation token.');

        $record->delete();

        $user = User::query()->where('role', UserRole::Owner)->first()
            ?? User::query()->first();

        abort_unless((bool) $user, 404, 'No users found for this tenant.');

        return $user;
    }
}
