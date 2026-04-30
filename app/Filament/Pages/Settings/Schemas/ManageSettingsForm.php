<?php

namespace App\Filament\Pages\Settings\Schemas;

use App\Enums\Orders\PaymentMethod;
use App\Filament\Forms\Components\MoneyInput;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
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
                            ->itemLabel(fn (array $state): ?string => isset($state['min_distance'], $state['max_distance'], $state['fee'])
                                ? "{$state['min_distance']}–{$state['max_distance']} mi · \${$state['fee']}"
                                : null),

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
                    ]),

                // Order Journey Section
                Section::make('Order Journey')
                    ->description('Customize the "What Happens Next" steps shown on the order confirmation page. The final step supports separate delivery and pickup copy.')
                    ->schema([
                        Repeater::make('order_journey_steps')
                            ->label('Journey Steps')
                            ->schema([
                                TextInput::make('title')
                                    ->required(),
                                TextInput::make('description')
                                    ->label('Description (general/pickup)'),
                                TextInput::make('description_delivery')
                                    ->label('Description (delivery variant)')
                                    ->helperText('Leave blank if same as above'),
                                TextInput::make('description_pickup')
                                    ->label('Description (pickup variant)')
                                    ->helperText('Leave blank if same as above'),
                            ])
                            ->defaultItems(3)
                            ->maxItems(6)
                            ->columnSpanFull(),
                    ]),

                // Catering Section
                Section::make('Catering')
                    ->description('Configure catering inquiry options')
                    ->schema([
                        TagsInput::make('catering_event_types')
                            ->label('Event Types')
                            ->placeholder('Add an event type')
                            ->helperText('Customers select from these options on the catering inquiry form (e.g. Wedding, Corporate Event, Birthday Party).')
                            ->reorderable()
                            ->columnSpanFull(),

                        TextInput::make('catering_deposit_percent')
                            ->label('Deposit Percent')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(25)
                            ->helperText('Used to compute the suggested deposit shown in quote emails and the "Mark Deposit Received" admin action. 0 disables deposit messaging.'),
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
                    ]),

                // Order Email Toggles — tenants can disable individual transactional
                // emails (e.g., skip "Baking" status emails but keep "Ready").
                Section::make('Order Emails')
                    ->description('Choose which order emails are sent to your customers. Disabling an email stops sending it entirely; customers will still see order status changes on the tracking page.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('email_order_placed_enabled')
                                    ->label('Order Placed')
                                    ->helperText('Sent when a customer completes checkout.'),

                                Toggle::make('email_order_confirmed_enabled')
                                    ->label('Order Confirmed')
                                    ->helperText('Sent when you confirm the order.'),

                                Toggle::make('email_order_baking_enabled')
                                    ->label('Baking')
                                    ->helperText('Sent when you start baking the order.'),

                                Toggle::make('email_order_ready_enabled')
                                    ->label('Ready')
                                    ->helperText('Sent when the order is ready for pickup or delivery.'),

                                Toggle::make('email_order_delivered_enabled')
                                    ->label('Delivered')
                                    ->helperText('Sent when the order is delivered or picked up.'),

                                Toggle::make('email_order_cancelled_enabled')
                                    ->label('Cancelled')
                                    ->helperText('Sent when an order is cancelled.'),

                                Toggle::make('email_order_message_enabled')
                                    ->label('Order Message Replies')
                                    ->helperText('Sent to the customer when you reply to their order message. Does not affect the notification you receive when a customer messages you.'),

                                Toggle::make('email_product_available_enabled')
                                    ->label('Back-in-Stock')
                                    ->helperText('Sent to waitlisted customers when you use "Notify Waitlist" on a product.'),
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

                        MoneyInput::make('revenue_cap')
                            ->label('Annual Revenue Cap')
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

                                MoneyInput::make('gift_card_default_amount')
                                    ->label('Default Selected Amount')
                                    ->default(25)
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
