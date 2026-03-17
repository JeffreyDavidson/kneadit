<?php

namespace App\Filament\Traits;

trait RequiresRole
{
    protected static function getRequiredRole(): string
    {
        return 'owner';
    }

    /**
     * Role hierarchy: owner > manager > staff.
     * A higher role can access resources requiring a lower role.
     */
    protected static function roleLevel(string $role): int
    {
        return match ($role) {
            'owner' => 3,
            'manager' => 2,
            'staff' => 1,
            default => 0,
        };
    }

    protected static function userMeetsRole(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return static::roleLevel($user->role) >= static::roleLevel(static::getRequiredRole());
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
