<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use App\Filament\Traits\RequiresRole;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

use App\Traits\HasPlanGating;
class ManageSettings extends Page
{
    use HasPlanGating, RequiresRole;

    use InteractsWithFormActions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 99;

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

            // Compliance
            Setting::set('allergy_disclaimer', $this->allergy_disclaimer);
            Setting::set('revenue_cap', $this->revenue_cap);

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

        Notification::make()
            ->title('Settings reset to defaults')
            ->info()
            ->send();
    }
}