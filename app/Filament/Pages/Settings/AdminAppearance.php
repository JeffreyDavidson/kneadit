<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Shared\PanelThemes;
use App\Services\Settings\SettingsManager;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AdminAppearance extends Page
{
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Admin Theme';

    protected static ?string $title = 'Admin Theme';

    protected string $view = 'filament.pages.settings.admin-appearance';

    public string $current = 'honey';

    public function mount(): void
    {
        $value = (string) rescue(
            fn () => resolve(SettingsManager::class)->get('admin_theme', 'honey'),
            'honey',
            false,
        );

        $this->current = array_key_exists($value, PanelThemes::AVAILABLE) ? $value : 'honey';
    }

    public function selectTheme(string $theme): void
    {
        if (! array_key_exists($theme, PanelThemes::AVAILABLE)) {
            return;
        }

        resolve(SettingsManager::class)->set('admin_theme', $theme);
        $this->current = $theme;

        Notification::make()
            ->title('Theme updated')
            ->body('Reload any open admin tabs to pick up the new palette.')
            ->success()
            ->send();

        // Force a full reload so the renderHook <style>:root{}</style> tag
        // re-renders with the new palette. Livewire's partial swap won't
        // replace the head injection on its own.
        $this->redirect(static::getUrl());
    }
}
