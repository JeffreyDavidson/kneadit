<?php

namespace App\Filament\Concerns;

use App\Enums\Staff\UserRole;
use Illuminate\Support\Facades\Auth;

trait RequiresManagerRole
{
    public static function canAccess(): bool
    {
        return static::hasManagerAccess();
    }

    protected static function hasManagerAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasMinRole(UserRole::Manager);
    }
}
