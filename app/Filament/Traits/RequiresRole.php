<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Auth;

trait RequiresRole
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->hasMinRole(static::getRequiredRole());
    }

    protected static function getRequiredRole(): string
    {
        return 'owner';
    }
}
