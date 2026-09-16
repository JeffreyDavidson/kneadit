<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    #[\Override]
    protected static string $resource = TenantResource::class;

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return TenantResource::getUrl('view', ['record' => $this->record]);
    }

    /** Notes are surfaced on the View page's Notes tab; don't duplicate them here. */
    #[\Override]
    public function getAllRelationManagers(): array
    {
        return [];
    }
}
