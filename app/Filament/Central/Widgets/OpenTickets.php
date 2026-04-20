<?php

namespace App\Filament\Central\Widgets;

use App\Models\Platform\SupportTicket;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpenTickets extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $count = SupportTicket::query()->open()->count();

        return [
            Stat::make('Open Tickets', $count)
                ->description('Awaiting response')
                ->color('danger')
                ->icon(Heroicon::OutlinedInbox)
                ->url(rescue(fn () => route('filament.central.resources.support-tickets.index', ['filters[status][value]' => 'open']), report: false)),
        ];
    }
}
