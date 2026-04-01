<?php

namespace App\Filament\Central\Resources\TenantResource\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Owner Information')
                    ->description('The person who owns this bakery account')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('id')
                                ->label('Subdomain / ID')
                                ->required()
                                ->unique(ignoreRecord: true)
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
                                ->options([
                                    'starter' => 'Starter ($9/mo)',
                                    'growth' => 'Growth ($19/mo)',
                                    'pro' => 'Pro ($29/mo)',
                                ])
                                ->required(),
                        ]),
                    ]),

                Section::make('Store Details')
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
                        TextInput::make('external_website')
                            ->label('External Website')
                            ->url()
                            ->placeholder('https://sweetbakes.com')
                            ->helperText('If baker has their own site (storefront skip mode)'),
                    ]),

                Section::make('Status')
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                            Toggle::make('storefront_enabled')
                                ->label('Storefront Enabled')
                                ->default(true),
                            DateTimePicker::make('trial_ends_at')
                                ->label('Trial Ends')
                                ->placeholder('N/A'),
                        ]),
                    ]),
            ]);
    }
}
