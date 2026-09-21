<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Auth;

trait RequiresManagerRole
{
    public static function canAccess(): bool
    {
        return static::hasManagerAccess();
    }

    protected static function hasManagerAccess(): bool
    {
        $user = Auth::guard('web')->user();

        return $user instanceof User
            && $user->role->meetsRequirement(UserRole::Manager);
    }
}
