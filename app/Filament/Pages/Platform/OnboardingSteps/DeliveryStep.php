<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
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
        $manager = app(SettingsManager::class);

        return [
            'delivery_enabled' => $manager->get('delivery_enabled', '0') === '1',
            'delivery_radius' => $manager->get('delivery_radius', ''),
            'delivery_fee' => $manager->get('delivery_fee', ''),
            'free_delivery_over' => (bool) $manager->get('free_delivery_minimum'),
            'free_delivery_threshold' => $manager->get('free_delivery_minimum', ''),
            'delivery_minimum_order' => $manager->get('delivery_minimum_order', ''),
            'pickup_enabled' => $manager->get('pickup_enabled', '1') === '1',
            'pickup_instructions' => $manager->get('pickup_instructions', ''),
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
                            MoneyInput::make('delivery.delivery_fee')
                                ->label('Flat Delivery Fee')
                                ->placeholder('5.00')
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled')),
                        ]),
                        Grid::make(2)->schema([
                            Toggle::make('delivery.free_delivery_over')
                                ->label('Free delivery over a certain amount?')
                                ->live()
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled')),
                            MoneyInput::make('delivery.free_delivery_threshold')
                                ->label('Free Delivery Threshold')
                                ->placeholder('50.00')
                                ->visible(fn (Get $get) => $get('delivery.delivery_enabled') && $get('delivery.free_delivery_over')),
                        ]),
                        MoneyInput::make('delivery.delivery_minimum_order')
                            ->label('Minimum Order for Delivery')
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
        app(SettingsManager::class)->setMany([
            'delivery_enabled' => $data['delivery_enabled'] ? '1' : '0',
            'delivery_radius' => $data['delivery_radius'],
            'delivery_fee' => $data['delivery_fee'],
            'free_delivery_minimum' => $data['free_delivery_over'] ? $data['free_delivery_threshold'] : null,
            'delivery_minimum_order' => $data['delivery_minimum_order'],
            'pickup_enabled' => $data['pickup_enabled'] ? '1' : '0',
            'pickup_instructions' => $data['pickup_instructions'],
        ]);
    }
}
