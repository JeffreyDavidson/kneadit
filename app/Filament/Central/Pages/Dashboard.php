<?php

namespace App\Filament\Central\Pages;

use App\Filament\Central\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'KneadIt Platform';

    public function getWidgets(): array
    {
        return [
            Widgets\PlatformInfo::class,
            Widgets\QuickActions::class,
            Widgets\PlatformStats::class,
            Widgets\OnboardingProgress::class,
            Widgets\RecentTenants::class,
        ];
    }
}
