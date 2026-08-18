<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class WelcomeStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'welcome';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $tenant = tenant();

        return [
            'bakery_name' => resolve(SettingsManager::class)->get('store_name')
                ?: ($tenant->store_name ?? $tenant->name ?? ''),
            'owner_name' => $tenant->name ?? '',
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Welcome')
            ->icon(Heroicon::OutlinedHandRaised)
            ->description('Tell us about your bakery')
            ->schema([
                Section::make('Welcome to KneadIt!')
                    ->description('Let\'s get your bakery set up. This will only take a few minutes.')
                    ->schema([
                        TextInput::make('welcome.bakery_name')
                            ->label('Bakery Name')
                            ->required()
                            ->placeholder('e.g. Sweet Sunrise Bakery')
                            ->maxLength(255),
                        TextInput::make('welcome.owner_name')
                            ->label('Your Name')
                            ->required()
                            ->placeholder('e.g. Jane Baker')
                            ->maxLength(255),
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->welcome));
    }

    public static function save(array $data): void
    {
        resolve(SettingsManager::class)->set('store_name', $data['bakery_name']);

        $tenant = tenant();
        if ($tenant) {
            $tenant->name = $data['owner_name'];
            $tenant->store_name = $data['bakery_name'];
            $tenant->save();
        }
    }
}
