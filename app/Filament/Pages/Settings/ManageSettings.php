<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Tenants\SaveTenantSettings;
use App\Enums\Customers\CateringEventType;
use App\Enums\Orders\PaymentMethod;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Pages\Settings\Schemas\ManageSettingsForm;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    use InteractsWithFormActions;
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.settings.manage-settings';

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

    /** @var array<int, string> */
    public ?array $payment_methods = [PaymentMethod::Cash->value];

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

    /** @var array<int, string> */
    public array $catering_event_types = [];

    public ?string $gift_card_preset_amounts = '';

    public ?int $gift_card_default_amount = 25;

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $this->store_name = settings('store_name', '');
        $this->store_email = settings('store_email', '');
        $this->store_phone = settings('store_phone', '');
        $this->store_address = settings('store_address', '');
        $this->default_daily_capacity = settings('default_daily_capacity', null);
        $this->minimum_order_lead_hours = settings('minimum_order_lead_hours', 48);
        $this->delivery_fee_tiers = settings('delivery_fee_tiers', '{"0-10": 5.00, "10-25": 3.00, "25+": 0.00}');
        $this->repeat_reminders_enabled = settings('repeat_reminders_enabled', false);
        $this->birthday_program_enabled = settings('birthday_program_enabled', false);
        $this->allergy_disclaimer = settings('allergy_disclaimer', 'Please inform us of any allergies or dietary restrictions when placing your order.');
        $this->revenue_cap = settings('revenue_cap', '250000');
        $methods = settings('payment_methods');
        $this->payment_methods = $methods ? json_decode($methods, true) : [PaymentMethod::Cash->value];
        $this->paypal_client_id = settings('paypal_client_id', '');
        $this->paypal_client_secret = settings('paypal_client_secret', '');
        $this->paypal_sandbox = (bool) settings('paypal_sandbox', true);
        $this->webhook_url = settings('webhook_url', '');
        $this->webhook_secret = settings('webhook_secret', '');

        $this->cancellation_policy = settings('cancellation_policy', '');
        $this->deposit_policy = settings('deposit_policy', '');
        $this->refund_policy = settings('refund_policy', '');
        $this->pickup_policy = settings('pickup_policy', '');
        $this->additional_terms = settings('additional_terms', '');
        $this->show_policies_on_storefront = (bool) settings('show_policies_on_storefront', false);

        $eventTypes = settings('catering_event_types');
        $decoded = $eventTypes ? json_decode($eventTypes, true) : null;
        $this->catering_event_types = is_array($decoded) && $decoded !== []
            ? array_values(array_filter($decoded, fn ($v) => is_string($v) && trim($v) !== ''))
            : CateringEventType::defaultLabels();

        $this->gift_card_preset_amounts = settings('gift_card_preset_amounts', '10,25,50,100');
        $this->gift_card_default_amount = (int) settings('gift_card_default_amount', 25);
    }

    public function content(Schema $schema): Schema
    {
        return ManageSettingsForm::configure($schema);
    }

    public function save(): void
    {
        try {
            resolve(SaveTenantSettings::class)(get_object_vars($this));

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
        $this->catering_event_types = CateringEventType::defaultLabels();
        $this->gift_card_preset_amounts = '10,25,50,100';
        $this->gift_card_default_amount = 25;

        Notification::make()
            ->title('Settings reset to defaults')
            ->info()
            ->send();
    }
}
