<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Filament\Support\AllowedFileTypes;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class BrandingStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'branding';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $tenant = tenant();
        $existingLogo = app(SettingsManager::class)->get('store_logo') ?: $tenant?->store_logo;

        return [
            'color_primary' => $tenant->brand_color_primary ?? '#6b4c3b',
            'color_secondary' => $tenant->brand_color_secondary ?? '#d4a574',
            'store_logo' => $existingLogo ? [$existingLogo] : [],
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Branding')
            ->icon(Heroicon::OutlinedPaintBrush)
            ->description('Make it yours')
            ->schema([
                Section::make('Brand Your Bakery')
                    ->description('Choose colors and upload your logo to personalize your storefront.')
                    ->schema([
                        Grid::make(2)->schema([
                            ColorPicker::make('branding.color_primary')
                                ->label('Primary Color')
                                ->required(),
                            ColorPicker::make('branding.color_secondary')
                                ->label('Secondary Color')
                                ->required(),
                        ]),
                        FileUpload::make('branding.store_logo')
                            ->label('Bakery Logo')
                            ->image()
                            ->acceptedFileTypes(AllowedFileTypes::IMAGES)
                            ->directory('logos')
                            ->maxSize(2048)
                            ->helperText('Upload your bakery logo (max 2MB). PNG or JPG recommended.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->branding));
    }

    public static function save(array $data): void
    {
        $settings = [
            'brand_color_primary' => $data['color_primary'],
            'brand_color_secondary' => $data['color_secondary'],
        ];

        $tenant = tenant();
        if ($tenant) {
            $tenant->brand_color_primary = $data['color_primary'];
            $tenant->brand_color_secondary = $data['color_secondary'];

            /** @var array<int, string>|string|null $storeLogo */
            $storeLogo = $data['store_logo'] ?? null;
            $logoPath = ! empty($storeLogo)
                ? (is_array($storeLogo) ? ($storeLogo[0] ?? null) : $storeLogo)
                : null;

            $settings['store_logo'] = $logoPath;
            $tenant->store_logo = $logoPath;

            $tenant->save();
        }

        app(SettingsManager::class)->setMany($settings);
    }
}
