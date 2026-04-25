<?php

namespace App\Filament\Central\Resources\TenantResource\Schemas;

use App\Enums\Platform\SubscriptionTier;
use App\Models\Platform\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account & Billing')
                    ->description('Owner identity, plan, and whether this tenant can use the system')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('id')
                                ->label('Subdomain / ID')
                                ->required()
                                ->unique()
                                ->alphaDash()
                                ->placeholder('sweet-bakes')
                                ->helperText('This becomes their subdomain: sweet-bakes.getkneadit.app')
                                ->disabled(fn (?Tenant $record) => $record !== null),
                            TextInput::make('name')
                                ->label('Owner Name')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->email()
                                ->required(),
                            Select::make('plan')
                                ->options(collect(SubscriptionTier::cases())
                                    ->mapWithKeys(fn (SubscriptionTier $tier) => [$tier->value => $tier->labelWithPrice()])
                                    ->all())
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            DateTimePicker::make('trial_ends_at')
                                ->label('Trial Ends')
                                ->placeholder('N/A')
                                ->helperText('Leave blank if this account isn\'t on a trial.'),
                            Toggle::make('is_active')
                                ->label('Account Active')
                                ->helperText('Turning this off blocks access to the admin panel and storefront.')
                                ->default(true),
                        ]),
                    ]),

                Section::make('Store & Storefront')
                    ->description('Public-facing details for the bakery\'s storefront')
                    ->icon(Heroicon::OutlinedBuildingStorefront)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('store_name')
                                ->label('Bakery Name')
                                ->placeholder('Sweet Bakes Co.'),
                            TextInput::make('custom_domain')
                                ->label('Custom Domain')
                                ->placeholder('sweetbakes.com')
                                ->helperText('Optional — baker can use their own domain'),
                        ]),
                        TextInput::make('external_website')
                            ->label('External Website')
                            ->url()
                            ->placeholder('https://sweetbakes.com')
                            ->helperText('If the baker hosts elsewhere, the storefront acts as a redirect (storefront-skip mode).')
                            ->columnSpanFull(),
                        Toggle::make('storefront_enabled')
                            ->label('Storefront Enabled')
                            ->helperText('When off, the public storefront shows "coming soon" instead of products.')
                            ->default(true),
                    ]),

                Section::make('Branding')
                    ->description('Colors used across the bakery\'s storefront and customer emails')
                    ->icon(Heroicon::OutlinedSwatch)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('brand_color_primary')
                                ->label('Primary Color')
                                ->type('color')
                                ->placeholder('#d4920c'),
                            TextInput::make('brand_color_secondary')
                                ->label('Secondary Color')
                                ->type('color')
                                ->placeholder('#1c1410'),
                        ]),
                    ]),
            ]);
    }
}
