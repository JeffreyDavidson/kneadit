<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use App\Filament\Forms\Components\MoneyInput;
use App\ValueObjects\Money;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

class OrderSettingsSection
{
    public static function make(): Section
    {
        return Section::make('Order Settings')
            ->description('Configure order processing and fulfillment settings')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('default_daily_capacity')
                            ->label('Default Daily Capacity')
                            ->numeric()
                            ->placeholder('100')
                            ->helperText('Maximum number of orders per day'),

                        TextInput::make('minimum_order_lead_hours')
                            ->label('Minimum Order Lead Hours')
                            ->numeric()
                            ->default(48)
                            ->helperText('Minimum hours before pickup/delivery'),
                    ]),

                Repeater::make('delivery_fee_tiers')
                    ->label('Delivery Fee Tiers')
                    ->helperText('Set how delivery fees scale with distance. Tiers should not overlap.')
                    ->columnSpanFull()
                    ->reorderable()
                    ->reorderableWithDragAndDrop()
                    ->addActionLabel('Add tier')
                    ->defaultItems(0)
                    ->columns(4)
                    ->schema([
                        TextInput::make('min_distance')
                            ->label('From')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->suffix('mi'),
                        TextInput::make('max_distance')
                            ->label('To')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->suffix('mi'),
                        MoneyInput::make('fee')
                            ->label('Fee')
                            ->required(),
                        TextInput::make('description')
                            ->label('Customer-facing label')
                            ->placeholder('Local delivery (0-5 miles)')
                            ->helperText('Shown to customers at checkout. Optional.'),
                    ])
                    ->itemLabel(function (array $state): ?string {
                        $minimum = Arr::get($state, 'min_distance');
                        $maximum = Arr::get($state, 'max_distance');
                        $fee = Arr::get($state, 'fee');

                        if (! is_numeric($minimum) || ! is_numeric($maximum) || ! is_numeric($fee)) {
                            return null;
                        }

                        return Number::format((float) $minimum) . '–' . Number::format((float) $maximum)
                            . ' mi · ' . Money::fromDollars($fee)->formatted();
                    }),

                Grid::make(2)
                    ->schema([
                        MoneyInput::make('minimum_pickup_order_amount')
                            ->label('Minimum Pickup Order')
                            ->default('0')
                            ->helperText('Minimum order subtotal for pickup (0 = no minimum)'),

                        MoneyInput::make('minimum_delivery_order_amount')
                            ->label('Minimum Delivery Order')
                            ->default('0')
                            ->helperText('Minimum order subtotal for delivery (0 = no minimum)'),
                    ]),

                TextInput::make('order_modification_window_minutes')
                    ->label('Order Modification Window (minutes)')
                    ->numeric()
                    ->default(0)
                    ->helperText('How long after placing an order a customer can edit quantities/tip. 0 disables the feature.'),

                Toggle::make('pickup_slots_enabled')
                    ->label('Enable Pickup Time Slots')
                    ->helperText('Replace the free-form pickup time field with discrete time slots based on your business hours.'),

                Toggle::make('sitewide_sale_enabled')
                    ->label('Enable Sitewide Sale')
                    ->helperText('Apply a percentage discount to every order. Stacks with coupons.'),

                Grid::make(2)
                    ->schema([
                        TextInput::make('sitewide_sale_percent')
                            ->label('Sale Percent (0–100)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->helperText('How much off the subtotal of every order.'),

                        TextInput::make('sitewide_sale_label')
                            ->label('Sale Label')
                            ->maxLength(60)
                            ->default('Sale')
                            ->helperText('Shown in the storefront banner and order receipt.'),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('pickup_slot_interval_minutes')
                            ->label('Slot Interval (minutes)')
                            ->numeric()
                            ->default(30)
                            ->helperText('How wide each pickup window is (e.g. 15, 30, 60).'),

                        TextInput::make('pickup_slot_max_per_window')
                            ->label('Max Orders per Slot')
                            ->numeric()
                            ->default(3)
                            ->helperText('Cap on how many orders can share a single pickup slot.'),
                    ]),
            ]);
    }
}
