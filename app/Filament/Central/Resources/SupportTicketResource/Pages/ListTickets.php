<?php

namespace App\Filament\Central\Resources\SupportTicketResource\Pages;

use App\Filament\Central\Resources\SupportTicketResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Tickets originate from the baker side; platform admin cannot create or edit
 * them, only reply/resolve via the ViewTicket page.
 */
class ListTickets extends ListRecords
{
    #[\Override]
    protected static string $resource = SupportTicketResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
