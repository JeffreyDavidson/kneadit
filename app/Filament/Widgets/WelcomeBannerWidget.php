<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Compact "Quick Actions" card on the tenant dashboard. Mirrors the
 * central panel's QuickActions widget pattern — single column,
 * eyebrow + button row.
 *
 * The class name and registered widget key (`welcome_banner`) are kept
 * for data compatibility with saved tenant dashboard configurations
 * that reference the legacy key. The widget previously rendered a
 * full-width banner with greeting, date, and stat tiles; that data is
 * now covered by `Today's Snapshot` (TodaysOrdersWidget) and other
 * dashboard widgets.
 */
class WelcomeBannerWidget extends Widget
{
    protected static ?int $sort = 0;

    /**
     * Quick Actions sits alone on its row — central does this by having the
     * next widget after QuickActions (columnSpan=1) be full-width, which
     * pushes everything else to the next row. Tenant achieves the same
     * visual by claiming a full-width slot here and constraining the card
     * width inside the view, leaving empty space to the right.
     */
    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome-banner';
}
