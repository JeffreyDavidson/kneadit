<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\DataTransferObjects\Settings\BrandingSettings;
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
        $tenant = self::tenant();
        $existingLogo = self::existingLogo();

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
                                ->regex(BrandingSettings::HEX_COLOR_PATTERN)
                                ->required(),
                            ColorPicker::make('branding.color_secondary')
                                ->label('Secondary Color')
                                ->regex(BrandingSettings::HEX_COLOR_PATTERN)
                                ->required(),
                        ]),
                        FileUpload::make('branding.store_logo')
                            ->label('Bakery Logo')
                            ->image()
                            ->acceptedFileTypes(AllowedFileTypes::IMAGES)
                            ->directory('logos')
                            ->maxSize(2048)
                            ->preventFilePathTampering(allowFilePathUsing: fn (string $file): bool => $file === self::existingLogo())
                            ->helperText('Upload your bakery logo (max 2MB). PNG or JPG recommended.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->afterValidation(fn (Step $component) => self::saveValidatedState($component));
    }

    /**
     * Saves from the step's validated state rather than the raw Livewire data,
     * so a newly uploaded logo is stored and the saved value is its stored path.
     */
    private static function saveValidatedState(Step $step): void
    {
        $state = $step->getChildSchema()?->getState() ?? [];

        $branding = array_filter(
            is_array($state['branding'] ?? null) ? $state['branding'] : [],
            is_string(...),
            ARRAY_FILTER_USE_KEY,
        );

        self::save($branding);
    }

    private static function existingLogo(): ?string
    {
        $logo = resolve(SettingsManager::class)->get('store_logo') ?: self::tenant()->store_logo;

        return is_string($logo) && $logo !== '' ? $logo : null;
    }

    public static function save(array $data): void
    {
        $settings = [
            'brand_color_primary' => $data['color_primary'],
            'brand_color_secondary' => $data['color_secondary'],
        ];

        $tenant = self::tenant();
        $tenant->brand_color_primary = is_string($data['color_primary'] ?? null) ? $data['color_primary'] : '#6b4c3b';
        $tenant->brand_color_secondary = is_string($data['color_secondary'] ?? null) ? $data['color_secondary'] : '#d4a574';

        $storeLogo = $data['store_logo'] ?? null;
        $logoPath = is_array($storeLogo) ? collect($storeLogo)->first(fn (mixed $path): bool => is_string($path)) : $storeLogo;
        $logoPath = is_string($logoPath) && $logoPath !== '' ? $logoPath : null;

        $settings['store_logo'] = $logoPath;
        $tenant->store_logo = $logoPath;

        $tenant->save();

        resolve(SettingsManager::class)->setMany($settings);
    }
}
