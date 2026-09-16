<?php

namespace App\Filament\Central\Pages;

use App\Filament\Central\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    #[\Override]
    protected static ?string $title = 'Dashboard';

    #[\Override]
    protected static ?string $navigationLabel = 'Dashboard';

    #[\Override]
    public function getWidgets(): array
    {
        return [
            Widgets\QuickActions::class,
            Widgets\PlatformStats::class,
            Widgets\RevenueOverview::class,
            Widgets\NeedsAttention::class,
            Widgets\OnboardingProgress::class,
            Widgets\RecentTenants::class,
            Widgets\RecentAuditLog::class,
        ];
    }
}
