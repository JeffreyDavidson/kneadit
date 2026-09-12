<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\TenantSettings;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class CompleteStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'complete';
    }

    public static function defaults(TenantSettings $settings): array
    {
        return [];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Complete')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->description('You\'re all set!')
            ->schema([
                View::make('filament.pages.platform.onboarding-complete'),
            ]);
    }

    public static function save(array $data): void
    {
        // Complete step uses completeOnboarding() on the page instead.
    }
}
