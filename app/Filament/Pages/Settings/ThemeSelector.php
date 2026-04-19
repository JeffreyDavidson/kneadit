<?php

namespace App\Filament\Pages\Settings;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Storefront\StorefrontTheme;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Settings\SettingsManager;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Laravel\Pennant\Feature;

class ThemeSelector extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Storefront Theme';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.settings.theme-selector';

    public function getTitle(): string
    {
        return 'Storefront Theme';
    }

    public function selectTheme(string $theme): void
    {
        if (! StorefrontTheme::tryFrom($theme)) {
            return;
        }

        app(SettingsManager::class)->set('storefront_theme', $theme);

        $this->dispatch('$refresh');

        Notification::make()
            ->title('Theme updated to ' . ucfirst($theme))
            ->success()
            ->send();
    }
}
