<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class ComplianceStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'compliance';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $manager = app(SettingsManager::class);

        return [
            'cottage_food_state' => $manager->get('cottage_food_state', ''),
            'revenue_cap' => $manager->get('revenue_cap', ''),
            'license_number' => $manager->get('license_number', ''),
            'allergy_disclaimer' => $manager->get('allergy_disclaimer', ''),
            'acknowledged' => $manager->get('compliance_acknowledged', '0') === '1',
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Compliance')
            ->icon(Heroicon::OutlinedShieldCheck)
            ->description('Cottage food compliance')
            ->schema([
                Section::make('Cottage Food Compliance')
                    ->description('Enter your state and compliance details. This helps ensure your bakery meets local regulations.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('compliance.cottage_food_state')
                                ->label('State')
                                ->required()
                                ->searchable()
                                ->options(self::usStates()),
                            TextInput::make('compliance.revenue_cap')
                                ->label('Annual Revenue Cap')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->helperText('Maximum annual revenue allowed under your state\'s cottage food law.'),
                        ]),
                        TextInput::make('compliance.license_number')
                            ->label('License / Permit Number')
                            ->placeholder('Optional')
                            ->maxLength(255),
                        Textarea::make('compliance.allergy_disclaimer')
                            ->label('Allergy Disclaimer')
                            ->required()
                            ->rows(4)
                            ->helperText('This disclaimer will be shown to customers on your storefront.'),
                        Checkbox::make('compliance.acknowledged')
                            ->label('I understand the cottage food laws in my state and confirm that my bakery complies with all applicable regulations.')
                            ->required()
                            ->accepted(),
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->compliance));
    }

    public static function save(array $data): void
    {
        app(SettingsManager::class)->setMany([
            'cottage_food_state' => $data['cottage_food_state'],
            'revenue_cap' => $data['revenue_cap'],
            'license_number' => $data['license_number'],
            'allergy_disclaimer' => $data['allergy_disclaimer'],
            'compliance_acknowledged' => $data['acknowledged'] ? '1' : '0',
        ]);
    }

    /** @return array<string, string> */
    public static function usStates(): array
    {
        return [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        ];
    }
}
