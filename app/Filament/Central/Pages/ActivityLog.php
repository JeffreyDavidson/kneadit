<?php

namespace App\Filament\Central\Pages;

use App\Models\PlatformActivity;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class ActivityLog extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|NITENUM|NULL $NAVIGATIONGROUP = 'Platform';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Activity Log';

    protected string $view = 'filament.central.pages.activity-log';

    public function getActivities()
    {
        return PlatformActivity::latest('created_at')->limit(100)->get();
    }

    public static function getEventIcon(string $event): string
    {
        return match ($event) {
            'tenant_created' => 'heroicon-o-plus-circle',
            'tenant_deactivated' => 'heroicon-o-x-circle',
            'plan_changed' => 'heroicon-o-arrow-path',
            'storefront_toggled' => 'heroicon-o-globe-alt',
            'trial_expired' => 'heroicon-o-clock',
            default => 'heroicon-o-information-circle',
        };
    }

    public static function getEventColor(string $event): string
    {
        return match ($event) {
            'tenant_created' => '#d4920c',
            'tenant_deactivated' => '#ef4444',
            'plan_changed' => '#e8b04a',
            'storefront_toggled' => '#8b6844',
            'trial_expired' => '#f5d88e',
            default => '#d4920c',
        };
    }
}
