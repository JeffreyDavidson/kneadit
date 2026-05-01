<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class NotificationSettingsSection
{
    public static function make(): Section
    {
        return Section::make('Notification Settings')
            ->description('Configure automated notifications and programs')
            ->schema([
                Grid::make(2)
                    ->schema([
                        Toggle::make('repeat_reminders_enabled')
                            ->label('Enable Repeat Order Reminders')
                            ->helperText('Send reminders for repeat customers'),

                        Toggle::make('birthday_program_enabled')
                            ->label('Enable Birthday Program')
                            ->helperText('Send birthday offers to customers'),

                        Toggle::make('low_stock_alerts_enabled')
                            ->label('Enable Daily Low-Stock Alerts')
                            ->helperText('Email you a daily digest of ingredients at or below their low-stock threshold (sent 7 AM).'),

                        Toggle::make('customer_referral_program_enabled')
                            ->label('Enable Customer Referral Program')
                            ->helperText('Customers get a unique referral link. New customers using a link get $X off; the referrer gets a coupon worth the same amount when their referral places an order.'),

                        Toggle::make('abandoned_cart_recovery_enabled')
                            ->label('Enable Abandoned Cart Recovery')
                            ->helperText('Email customers who left items in their cart and didn\'t check out. Only applies to customers who entered their email.'),
                    ]),

                TextInput::make('customer_referral_discount_dollars')
                    ->label('Referral Discount ($)')
                    ->numeric()
                    ->default(10)
                    ->helperText('Both the referee and referrer get this discount amount.'),

                Grid::make(2)
                    ->schema([
                        TextInput::make('abandoned_cart_recovery_hours')
                            ->label('Abandonment Threshold (hours)')
                            ->numeric()
                            ->default(24)
                            ->helperText('How long a cart must sit idle before a recovery email is sent.'),

                        TextInput::make('abandoned_cart_recovery_coupon_dollars')
                            ->label('Recovery Coupon ($)')
                            ->numeric()
                            ->default(5)
                            ->helperText('Single-use coupon included in the recovery email. 0 disables the coupon (email still sent).'),
                    ]),
            ]);
    }
}
