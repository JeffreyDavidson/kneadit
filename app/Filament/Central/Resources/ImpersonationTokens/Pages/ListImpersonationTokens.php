<?php

namespace App\Filament\Central\Resources\ImpersonationTokens\Pages;

use App\Filament\Central\Resources\ImpersonationTokens\ImpersonationTokenResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Audit log of every "Login as Baker" impersonation token minted from
 * the Tenant view page. Tokens are retained after consumption so we
 * have a permanent trail of who logged into whose account, when, and
 * from what IP.
 */
class ListImpersonationTokens extends ListRecords
{
    protected static string $resource = ImpersonationTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
