<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;
use App\Filament\Traits\RequiresRole;

use App\Traits\HasPlanGating;
class ThemeSelector extends Page
{
    use HasPlanGating, RequiresRole;

    protected static string $requiredPlan = 'pro';
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

        Setting::set('storefront_theme', $theme);

        $this->dispatch('$refresh');

        \Filament\Notifications\Notification::make()
            ->title('Theme updated to ' . ucfirst($theme))
            ->success()
            ->send();
    }
}
