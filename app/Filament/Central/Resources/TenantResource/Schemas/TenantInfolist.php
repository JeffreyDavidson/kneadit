<?php

namespace App\Filament\Central\Resources\TenantResource\Schemas;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class TenantInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Store Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('store_name')
                                ->label('Bakery Name')
                                ->size('lg')
                                ->weight(FontWeight::Bold)
                                ->placeholder('Not set'),
                            TextEntry::make('id')
                                ->label('Subdomain URL')
                                ->formatStateUsing(fn (Tenant $record, TenantUrlGenerator $urls): string => $urls->storefrontHost($record))
                                ->url(fn (Tenant $record, TenantUrlGenerator $urls): string => $urls->storefront($record))
                                ->openUrlInNewTab(),
                            TextEntry::make('custom_domain')
                                ->label('Custom Domain')
                                ->placeholder('None'),
                            TextEntry::make('external_website')
                                ->label('External Website')
                                ->url(fn (?string $state) => $state)
                                ->openUrlInNewTab()
                                ->placeholder('None'),
                        ]),
                    ]),

                Section::make('Owner Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Owner Name'),
                            TextEntry::make('email')
                                ->label('Email')
                                ->copyable(),
                        ]),
                    ]),

                Section::make('Plan & Status')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('plan')
                                ->badge(),
                            TextEntry::make('is_active')
                                ->label('Active')
                                ->badge()
                                ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                                ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                            TextEntry::make('storefront_enabled')
                                ->label('Storefront')
                                ->badge()
                                ->formatStateUsing(fn (bool $state) => $state ? 'Enabled' : 'Disabled')
                                ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('trial_ends_at')
                                ->label('Trial Ends')
                                ->dateTime()
                                ->placeholder('No trial'),
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime(),
                        ]),
                    ]),

                Section::make('Branding')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('brand_color_primary')
                                ->label('Primary Color')
                                ->placeholder('Not set'),
                            TextEntry::make('brand_color_secondary')
                                ->label('Secondary Color')
                                ->placeholder('Not set'),
                        ]),
                    ]),
            ]);
    }
}
