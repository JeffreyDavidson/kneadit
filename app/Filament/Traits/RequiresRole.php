<?php

namespace App\Filament\Traits;

use App\Enums\UserRole;
use App\Models\User;

trait RequiresRole
{
    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Owner;
    }

    protected static function userMeetsRole(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasMinRole(static::getRequiredRole());
    }

    public static function canCreate(): bool
    {
        return static::userMeetsRole();
    }

    public static function canEdit($record): bool
    {
        return static::userMeetsRole();
    }

    public static function canDelete($record): bool
    {
        return static::userMeetsRole();
    }
}
