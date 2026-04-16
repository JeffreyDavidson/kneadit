<?php

namespace App\Filament\Pages\Settings\Schemas;

use App\Enums\Orders\PaymentMethod;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ManageSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Store Information Section
                Section::make('Store Information')
                    ->description('Basic information about your bakery')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('store_name')
                                    ->label('Store Name')
                                    ->required()
                                    ->placeholder('Your Bakery Name'),

                                TextInput::make('store_email')
                                    ->label('Store Email')
                                    ->email()
                                    ->placeholder('contact@yourbakery.com'),

                                TextInput::make('store_phone')
                                    ->label('Store Phone')
                                    ->tel()
                                    ->placeholder('+1 (555) 123-4567'),

                                TextInput::make('store_address')
                                    ->label('Store Address')
                                    ->placeholder('123 Baker Street, City, State 12345')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // Order Settings Section
                Section::make('Order Settings')
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

                        Textarea::make('delivery_fee_tiers')
                            ->label('Delivery Fee Tiers (JSON)')
                            ->placeholder('{"0-10": 5.00, "10-25": 3.00, "25+": 0.00}')
                            ->helperText('JSON format: distance ranges and fees')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // Notification Settings Section
                Section::make('Notification Settings')
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
                            ]),
                    ]),

                // Payment Settings
                Section::make('Payment Methods')
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
                    ]),

                // Compliance Section
                Section::make('Compliance & Legal')
                    ->description('Legal disclaimers and business compliance settings')
                    ->schema([
                        Textarea::make('allergy_disclaimer')
                            ->label('Allergy Disclaimer')
                            ->placeholder('Please inform us of any allergies or dietary restrictions...')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('revenue_cap')
                            ->label('Annual Revenue Cap')
                            ->numeric()
                            ->prefix('$')
                            ->default('250000')
                            ->helperText('Annual revenue limit for compliance')
                            ->columnSpanFull(),

                        Textarea::make('cancellation_policy')
                            ->label('Cancellation Policy')
                            ->placeholder('Orders cancelled within 48 hours of pickup are non-refundable.')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('deposit_policy')
                            ->label('Deposit Policy')
                            ->placeholder('A 50% deposit is required to secure your order. Remaining balance due at pickup.')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('refund_policy')
                            ->label('Refund Policy')
                            ->placeholder('No refunds on custom orders. Store credit may be offered at our discretion.')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('pickup_policy')
                            ->label('Pickup Policy')
                            ->placeholder('Orders not picked up within 2 hours of scheduled time will be forfeited.')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('additional_terms')
                            ->label('Additional Terms')
                            ->placeholder('Any other terms or conditions...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('show_policies_on_storefront')
                            ->label('Show Policies on Storefront')
                            ->helperText('Display your policies in the storefront footer'),
                    ]),

                // Gift Card Settings
                Section::make('Gift Cards')
                    ->description('Configure gift card purchase options on your storefront')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('gift_card_preset_amounts')
                                    ->label('Preset Amounts')
                                    ->placeholder('10,25,50,100')
                                    ->helperText('Comma-separated dollar amounts shown as quick-select buttons'),

                                TextInput::make('gift_card_default_amount')
                                    ->label('Default Selected Amount')
                                    ->numeric()
                                    ->default(25)
                                    ->prefix('$')
                                    ->helperText('The amount pre-selected when the page loads'),
                            ]),
                    ]),

                // Integrations Section
                Section::make('Integrations')
                    ->description('Connect KneadIt to other tools via webhooks')
                    ->schema([
                        TextInput::make('webhook_url')
                            ->label('Webhook URL')
                            ->url()
                            ->placeholder('https://hooks.zapier.com/...')
                            ->helperText('We\'ll send order events (created, updated) to this URL')
                            ->columnSpanFull(),

                        TextInput::make('webhook_secret')
                            ->label('Webhook Secret')
                            ->password()
                            ->placeholder('Optional signing secret for verification')
                            ->helperText('Used to sign webhook payloads (X-KneadIt-Signature header)')
                            ->columnSpanFull(),
                    ]),

                // Save Actions
                Actions::make([
                    Action::make('save')
                        ->label('Save Settings')
                        ->color('primary')
                        ->action('save'),

                    Action::make('reset')
                        ->label('Reset to Defaults')
                        ->color('gray')
                        ->action('resetToDefaults')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Settings')
                        ->modalDescription('Are you sure you want to reset all settings to their default values?'),
                ])
                    ->alignEnd()
                    ->columnSpanFull(),
            ]);
    }
}
