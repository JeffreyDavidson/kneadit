<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

final class DeliveryStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'delivery';
    }

    public static function defaults(TenantSettings $settings): array
    {
        return [
            'delivery_enabled' => false,
            'delivery_radius' => '',
            'delivery_fee' => '',
            'free_delivery_over' => false,
            'free_delivery_threshold' => '',
            'delivery_minimum_order' => '',
            'pickup_enabled' => true,
            'pickup_instructions' => '',
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Delivery')
            ->icon(Heroicon::OutlinedTruck)
            ->description('Delivery & pickup options')
            ->schema([
                Section::make('Delivery Settings')
                    ->description('Configure how customers receive their orders.')
                    ->schema([
                        Toggle::make('delivery.delivery_enabled')
                            ->label('Do you offer delivery?')
                            ->live()
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('delivery.delivery_radius')
                                ->label('Delivery Radius (miles)')
                                ->numeric()
                                ->placeholder('15')
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled')),
                            TextInput::make('delivery.delivery_fee')
                                ->label('Flat Delivery Fee')
                                ->numeric()
                                ->prefix('$')
                                ->placeholder('5.00')
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled')),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('delivery.free_delivery_over')
                                ->label('Free delivery over a certain amount?')
                                ->live()
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled')),
                            TextInput::make('delivery.free_delivery_threshold')
                                ->label('Free Delivery Threshold')
                                ->numeric()
                                ->prefix('$')
                                ->placeholder('50.00')
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled') && $get('delivery.free_delivery_over')),
                        ]),
                        TextInput::make('delivery.delivery_minimum_order')
                            ->label('Minimum Order for Delivery')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('20.00')
                            ->visible(fn (Get $get) => $get('delivery.delivery_enabled')),

                        Toggle::make('delivery.pickup_enabled')
                            ->label('Do you offer pickup?')
                            ->live()
                            ->columnSpanFull(),
                        Textarea::make('delivery.pickup_instructions')
                            ->label('Pickup Instructions')
                            ->placeholder('e.g. Pick up at the side door, ring the bell...')
                            ->rows(3)
                            ->required(fn (Get $get) => $get('delivery.pickup_enabled'))
                            ->visible(fn (Get $get) => $get('delivery.pickup_enabled'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->afterValidation(function () use ($page) {
                if (! $page->delivery['delivery_enabled'] && ! $page->delivery['pickup_enabled']) {
                    throw ValidationException::withMessages([
                        'delivery.pickup_enabled' => 'You must enable at least one option: pickup or delivery.',
                    ]);
                }
                self::save($page->delivery);
            });
    }

    public static function save(array $data): void
    {
        settings(['delivery_enabled' => $data['delivery_enabled'] ? '1' : '0']);
        settings(['delivery_radius' => $data['delivery_radius']]);
        settings(['delivery_fee' => $data['delivery_fee']]);
        settings(['free_delivery_threshold' => $data['free_delivery_over'] ? $data['free_delivery_threshold'] : null]);
        settings(['delivery_minimum_order' => $data['delivery_minimum_order']]);
        settings(['pickup_enabled' => $data['pickup_enabled'] ? '1' : '0']);
        settings(['pickup_instructions' => $data['pickup_instructions']]);
    }
}
