<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\TenantSettings;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class PreviewStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'preview';
    }

    public static function defaults(TenantSettings $settings): array
    {
        return [];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->description('Review your storefront')
            ->schema([
                View::make('filament.pages.platform.onboarding-preview')
                    ->viewData([
                        'page' => $page,
                    ]),
            ]);
    }

    public static function save(array $data): void
    {
        // Preview step has no data to save.
    }
}
