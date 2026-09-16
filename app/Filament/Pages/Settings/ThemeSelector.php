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

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Storefront Theme';

    #[\Override]
    protected static ?int $navigationSort = 5;

    #[\Override]
    protected string $view = 'filament.pages.settings.theme-selector';

    #[\Override]
    public function getTitle(): string
    {
        return 'Storefront Theme';
    }

    public function selectTheme(string $theme): void
    {
        if (! StorefrontTheme::tryFrom($theme)) {
            return;
        }

        resolve(SettingsManager::class)->set('storefront_theme', $theme);

        $this->dispatch('$refresh');

        Notification::make()
            ->title('Theme updated to '.ucfirst($theme))
            ->success()
            ->send();
    }
}
