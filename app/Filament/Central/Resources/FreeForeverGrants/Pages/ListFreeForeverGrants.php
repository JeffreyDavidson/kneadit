<?php

namespace App\Filament\Central\Resources\FreeForeverGrants\Pages;

use App\Filament\Central\Resources\FreeForeverGrants\FreeForeverGrantResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Audit ledger for free-forever grants. Grants are issued via the Tenants
 * list bulk action; admins land here to see who has been comped, by whom,
 * and to revoke if needed.
 */
class ListFreeForeverGrants extends ListRecords
{
    protected static string $resource = FreeForeverGrantResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
