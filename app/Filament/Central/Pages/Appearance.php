<?php

namespace App\Filament\Central\Pages;

use App\Filament\Shared\PanelThemes;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Appearance extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Appearance';

    protected string $view = 'filament.central.pages.appearance';

    public string $current = 'honey';

    public function mount(): void
    {
        $this->current = PanelThemes::current();
    }

    public function selectTheme(string $theme): void
    {
        if (! array_key_exists($theme, PanelThemes::AVAILABLE)) {
            return;
        }

        platformSettings(['central_theme' => $theme]);
        $this->current = $theme;

        Notification::make()
            ->title('Theme updated')
            ->body('Reload any open tabs to pick up the new palette everywhere.')
            ->success()
            ->send();

        // Force a full reload so the injected <style> tag re-renders with the
        // new palette — Livewire's partial swap won't replace it on its own.
        $this->redirect(static::getUrl());
    }
}
