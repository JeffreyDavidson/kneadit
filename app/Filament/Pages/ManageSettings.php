<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    use HasPlanGating, RequiresRole;
    use InteractsWithFormActions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.manage-settings';

    protected static ?string $title = 'Manage Settings';

    // Form data properties
    public ?string $store_name = '';

    public ?string $store_email = '';

    public ?string $store_phone = '';

    public ?string $store_address = '';

    public ?int $default_daily_capacity = null;

    public ?int $minimum_order_lead_hours = 48;

    public ?string $delivery_fee_tiers = '';

    public bool $repeat_reminders_enabled = false;

    public bool $birthday_program_enabled = false;

    public ?string $allergy_disclaimer = '';

    public ?string $revenue_cap = '250000';

    public ?array $payment_methods = ['cash'];

    public ?string $paypal_client_id = '';

    public ?string $paypal_client_secret = '';

    public bool $paypal_sandbox = true;

    public string $webhook_url = '';

    public string $webhook_secret = '';

    public ?string $cancellation_policy = '';

    public ?string $deposit_policy = '';

    public ?string $refund_policy = '';

    public ?string $pickup_policy = '';

    public ?string $additional_terms = '';

    public bool $show_policies_on_storefront = false;

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $this->store_name = Setting::get('store_name', '');
        $this->store_email = Setting::get('store_email', '');
        $this->store_phone = Setting::get('store_phone', '');
        $this->store_address = Setting::get('store_address', '');
        $this->default_daily_capacity = Setting::get('default_daily_capacity', null);
        $this->minimum_order_lead_hours = Setting::get('minimum_order_lead_hours', 48);
        $this->delivery_fee_tiers = Setting::get('delivery_fee_tiers', '{"0-10": 5.00, "10-25": 3.00, "25+": 0.00}');
        $this->repeat_reminders_enabled = Setting::get('repeat_reminders_enabled', false);
        $this->birthday_program_enabled = Setting::get('birthday_program_enabled', false);
        $this->allergy_disclaimer = Setting::get('allergy_disclaimer', 'Please inform us of any allergies or dietary restrictions when placing your order.');
        $this->revenue_cap = Setting::get('revenue_cap', '250000');
        $methods = Setting::get('payment_methods');
        $this->payment_methods = $methods ? json_decode($methods, true) : ['cash'];
        $this->paypal_client_id = Setting::get('paypal_client_id', '');
        $this->paypal_client_secret = Setting::get('paypal_client_secret', '');
        $this->paypal_sandbox = (bool) Setting::get('paypal_sandbox', true);
        $this->webhook_url = Setting::get('webhook_url', '');
        $this->webhook_secret = Setting::get('webhook_secret', '');

        $this->cancellation_policy = Setting::get('cancellation_policy', '');
        $this->deposit_policy = Setting::get('deposit_policy', '');
        $this->refund_policy = Setting::get('refund_policy', '');
        $this->pickup_policy = Setting::get('pickup_policy', '');
        $this->additional_terms = Setting::get('additional_terms', '');
        $this->show_policies_on_storefront = (bool) Setting::get('show_policies_on_storefront', false);
    }

    public function content(Schema $schema): Schema
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
                                'stripe' => 'Stripe — Credit cards, Apple Pay, Google Pay',
                                'paypal' => 'PayPal — Accept payments through PayPal Business',
                                'cash' => 'Cash / Manual — In person (cash, Venmo, Zelle, etc.)',
                            ])
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        View::make('filament.pages.stripe-connect-status')
                            ->visible(fn (Get $get) => in_array('stripe', $get('payment_methods') ?? [])),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('paypal_client_id')
                                    ->label('PayPal Client ID')
                                    ->placeholder('Your PayPal Client ID'),
                                TextInput::make('paypal_client_secret')
                                    ->label('PayPal Client Secret')
                                    ->password(),
                            ])
                            ->visible(fn (Get $get) => in_array('paypal', $get('payment_methods') ?? [])),

                        Toggle::make('paypal_sandbox')
                            ->label('PayPal Sandbox Mode')
                            ->helperText('Enable to test payments without real money')
                            ->visible(fn (Get $get) => in_array('paypal', $get('payment_methods') ?? [])),
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

    public function save(): void
    {
        try {
            // Store Information
            Setting::set('store_name', $this->store_name);
            Setting::set('store_email', $this->store_email);
            Setting::set('store_phone', $this->store_phone);
            Setting::set('store_address', $this->store_address);

            // Order Settings
            Setting::set('default_daily_capacity', $this->default_daily_capacity);
            Setting::set('minimum_order_lead_hours', $this->minimum_order_lead_hours);
            Setting::set('delivery_fee_tiers', $this->delivery_fee_tiers);

            // Notification Settings
            Setting::set('repeat_reminders_enabled', $this->repeat_reminders_enabled);
            Setting::set('birthday_program_enabled', $this->birthday_program_enabled);

            // Payment Methods
            Setting::set('payment_methods', json_encode($this->payment_methods));
            Setting::set('payment_method', $this->payment_methods[0] ?? 'cash');
            if (in_array('paypal', $this->payment_methods)) {
                Setting::set('paypal_client_id', $this->paypal_client_id);
                Setting::set('paypal_client_secret', $this->paypal_client_secret);
                Setting::set('paypal_sandbox', $this->paypal_sandbox ? '1' : '0');
                Setting::set('webhook_url', $this->webhook_url);
                Setting::set('webhook_secret', $this->webhook_secret);
            }

            // Compliance
            Setting::set('allergy_disclaimer', $this->allergy_disclaimer);
            Setting::set('revenue_cap', $this->revenue_cap);
            Setting::set('cancellation_policy', $this->cancellation_policy);
            Setting::set('deposit_policy', $this->deposit_policy);
            Setting::set('refund_policy', $this->refund_policy);
            Setting::set('pickup_policy', $this->pickup_policy);
            Setting::set('additional_terms', $this->additional_terms);
            Setting::set('show_policies_on_storefront', $this->show_policies_on_storefront ? '1' : '0');

            Notification::make()
                ->title('Settings saved successfully!')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving settings')
                ->body('There was an error saving your settings. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function resetToDefaults(): void
    {
        // Reset to default values
        $this->store_name = '';
        $this->store_email = '';
        $this->store_phone = '';
        $this->store_address = '';
        $this->default_daily_capacity = null;
        $this->minimum_order_lead_hours = 48;
        $this->delivery_fee_tiers = '{"0-10": 5.00, "10-25": 3.00, "25+": 0.00}';
        $this->repeat_reminders_enabled = false;
        $this->birthday_program_enabled = false;
        $this->allergy_disclaimer = 'Please inform us of any allergies or dietary restrictions when placing your order.';
        $this->revenue_cap = '250000';
        $this->cancellation_policy = '';
        $this->deposit_policy = '';
        $this->refund_policy = '';
        $this->pickup_policy = '';
        $this->additional_terms = '';
        $this->show_policies_on_storefront = false;

        Notification::make()
            ->title('Settings reset to defaults')
            ->info()
            ->send();
    }
}
