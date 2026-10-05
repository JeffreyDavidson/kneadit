<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use App\Enums\Orders\PaymentMethod;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Illuminate\Support\Facades\Gate;

class PaymentMethodsSection
{
    public static function make(): Section
    {
        return Section::make('Payment Methods')
            ->visible(fn (): bool => Gate::allows('manage-payments'))
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
                    ->visible(fn (Get $get): bool => in_array(PaymentMethod::Stripe->value, self::selectedMethods($get), true)),

                Grid::make(2)
                    ->schema([
                        TextInput::make('paypal_client_id')
                            ->label('PayPal Client ID')
                            ->placeholder('Your PayPal Client ID'),
                        TextInput::make('paypal_client_secret')
                            ->label('PayPal Client Secret')
                            ->password()
                            ->placeholder(fn (): string => filled(settings('paypal_client_secret')) ? 'Set — enter a new value to replace it' : 'Your PayPal Client Secret'),
                    ])
                    ->visible(fn (Get $get): bool => in_array(PaymentMethod::PayPal->value, self::selectedMethods($get), true)),

                Textarea::make('paypal_invoice_terms')
                    ->label('PayPal Invoice Terms')
                    ->rows(2)
                    ->placeholder('Payment due within 30 days.')
                    ->helperText('Terms printed on invoices sent through PayPal.')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => in_array(PaymentMethod::PayPal->value, self::selectedMethods($get), true)),

                Toggle::make('paypal_sandbox')
                    ->label('PayPal Sandbox Mode')
                    ->helperText('Enable to test payments without real money')
                    ->visible(fn (Get $get): bool => in_array(PaymentMethod::PayPal->value, self::selectedMethods($get), true)),
            ]);
    }

    /** @return list<string> */
    private static function selectedMethods(Get $get): array
    {
        $methods = $get('payment_methods');

        if (! is_array($methods)) {
            return [];
        }

        return array_values(array_filter($methods, is_string(...)));
    }
}
