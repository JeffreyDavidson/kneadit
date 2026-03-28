<?php

namespace App\Filament\Central\Widgets;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpenTickets extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $count = SupportTicket::query()->where('status', SupportTicketStatus::Open)->count();

        return [
            Stat::make('Open Tickets', $count)
                ->description('Awaiting response')
                ->color('danger')
                ->icon('heroicon-o-inbox')
                ->url(rescue(fn () => route('filament.central.resources.support-tickets.index', ['tableFilters[status][value]' => 'open']), report: false)),
        ];
    }
}
