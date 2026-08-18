<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use App\Enums\Orders\PaymentMethod;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

class PaymentMethodsSection
{
    public static function make(): Section
    {
        return Section::make('Payment Methods')
            ->description('Configure how you collect payments from customers')
            ->schema([
                CheckboxList::make('payment_methods')
                    ->label('Accepted Payment Methods')
                    ->options([
                        PaymentMethod::Stripe->value => 'Stripe — Credit cards, Apple Pay, Google Pay',
                        PaymentMethod::PayPal->value => 'PayPal — Accept payments through PayPal Business',
                        PaymentMethod::Cash->value => 'Cash / Manual — In person (cash, Venmo, Zelle, etc.)',
                    ])
                    ->required()
                    ->live()
                    ->columnSpanFull(),

                View::make('filament.pages.shared.stripe-connect-status')
                    ->visible(fn (Get $get) => in_array(PaymentMethod::Stripe->value, $get('payment_methods') ?? [])),

                Grid::make(2)
                    ->schema([
                        TextInput::make('paypal_client_id')
                            ->label('PayPal Client ID')
                            ->placeholder('Your PayPal Client ID'),
                        TextInput::make('paypal_client_secret')
                            ->label('PayPal Client Secret')
                            ->password(),
                    ])
                    ->visible(fn (Get $get) => in_array(PaymentMethod::PayPal->value, $get('payment_methods') ?? [])),

                Toggle::make('paypal_sandbox')
                    ->label('PayPal Sandbox Mode')
                    ->helperText('Enable to test payments without real money')
                    ->visible(fn (Get $get) => in_array(PaymentMethod::PayPal->value, $get('payment_methods') ?? [])),
            ]);
    }
}
