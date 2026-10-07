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
        $tenant = self::tenant();

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
                Section::make(self::greeting($page->welcome['bakery_name'] ?? null))
                    ->description('Setup takes about 5 minutes. You can change everything later.')
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

    private static function greeting(mixed $bakeryName): string
    {
        if (! is_string($bakeryName) || trim($bakeryName) === '') {
            return 'Let\'s get your bakery ready';
        }

        return "Let's get {$bakeryName} ready";
    }

    public static function save(array $data): void
    {
        resolve(SettingsManager::class)->set('store_name', $data['bakery_name']);

        $tenant = self::tenant();
        $tenant->name = is_string($data['owner_name'] ?? null) ? $data['owner_name'] : '';
        $tenant->store_name = is_string($data['bakery_name'] ?? null) ? $data['bakery_name'] : '';
        $tenant->save();
    }
}
