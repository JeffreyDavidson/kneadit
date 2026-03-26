<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class ThemeSelector extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Storefront Theme';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.theme-selector';

    public function getTitle(): string
    {
        return 'Storefront Theme';
    }

    public function selectTheme(string $theme): void
    {
        if (! in_array($theme, ['classic', 'modern', 'rustic', 'elegant'])) {
            return;
        }

        settings(['storefront_theme' => $theme]);

        $this->dispatch('$refresh');

        Notification::make()
            ->title('Theme updated to '.ucfirst($theme))
            ->success()
            ->send();
    }
}
