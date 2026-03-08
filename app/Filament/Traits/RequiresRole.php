<?php

namespace App\Filament\Traits;

trait RequiresRole
{
    protected static function getRequiredRole(): string
    {
        return 'owner';
    }
}
