<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Operations\RegenerateWebhookSecret;
use App\Actions\Operations\SendTestWebhook;
use App\Actions\Tenants\SaveTenantSettings;
use App\Enums\Orders\PaymentMethod;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Pages\Settings\Schemas\ManageSettingsForm;
use App\Services\Settings\TenantSettingsDefaults;
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

    /** @var array<int, array<string, mixed>> */
    public array $delivery_fee_tiers = [];

    public ?string $minimum_pickup_order_amount = '0';

    public ?string $minimum_delivery_order_amount = '0';

    public bool $repeat_reminders_enabled = false;

    public bool $birthday_program_enabled = false;

    public bool $email_order_placed_enabled = true;

    public bool $email_order_confirmed_enabled = true;

    public bool $email_order_baking_enabled = true;

    public bool $email_order_ready_enabled = true;

    public bool $email_order_delivered_enabled = true;

    public bool $email_order_cancelled_enabled = true;

    public bool $email_order_message_enabled = true;

    public bool $email_product_available_enabled = true;

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

    /** @var array<int, array<string, string>> */
    public array $order_journey_steps = [];

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $defaults = TenantSettingsDefaults::all();

        $this->store_name = settings('store_name', $defaults['store_name']);
        $this->store_email = settings('store_email', $defaults['store_email']);
        $this->store_phone = settings('store_phone', $defaults['store_phone']);
        $this->store_address = settings('store_address', $defaults['store_address']);
        $this->default_daily_capacity = settings('default_daily_capacity', $defaults['default_daily_capacity']);
        $this->minimum_order_lead_hours = settings('minimum_order_lead_hours', $defaults['minimum_order_lead_hours']);
        $storedTiers = settings('delivery_fee_tiers', $defaults['delivery_fee_tiers']);
        $decodedTiers = is_string($storedTiers) ? json_decode($storedTiers, true) : $storedTiers;
        $this->delivery_fee_tiers = is_array($decodedTiers) ? array_values($decodedTiers) : [];
        $this->minimum_pickup_order_amount = settings('minimum_pickup_order_amount', $defaults['minimum_pickup_order_amount']);
        $this->minimum_delivery_order_amount = settings('minimum_delivery_order_amount', $defaults['minimum_delivery_order_amount']);
        $this->repeat_reminders_enabled = settings('repeat_reminders_enabled', $defaults['repeat_reminders_enabled']);
        $this->birthday_program_enabled = settings('birthday_program_enabled', $defaults['birthday_program_enabled']);
        $this->email_order_placed_enabled = (bool) settings('email_order_placed_enabled', $defaults['email_order_placed_enabled']);
        $this->email_order_confirmed_enabled = (bool) settings('email_order_confirmed_enabled', $defaults['email_order_confirmed_enabled']);
        $this->email_order_baking_enabled = (bool) settings('email_order_baking_enabled', $defaults['email_order_baking_enabled']);
        $this->email_order_ready_enabled = (bool) settings('email_order_ready_enabled', $defaults['email_order_ready_enabled']);
        $this->email_order_delivered_enabled = (bool) settings('email_order_delivered_enabled', $defaults['email_order_delivered_enabled']);
        $this->email_order_cancelled_enabled = (bool) settings('email_order_cancelled_enabled', $defaults['email_order_cancelled_enabled']);
        $this->email_order_message_enabled = (bool) settings('email_order_message_enabled', $defaults['email_order_message_enabled']);
        $this->email_product_available_enabled = (bool) settings('email_product_available_enabled', $defaults['email_product_available_enabled']);
        $this->allergy_disclaimer = settings('allergy_disclaimer', $defaults['allergy_disclaimer']);
        $this->revenue_cap = settings('revenue_cap', $defaults['revenue_cap']);

        $methods = settings('payment_methods');
        $this->payment_methods = $methods ? json_decode($methods, true) : $defaults['payment_methods'];

        $this->paypal_client_id = settings('paypal_client_id', $defaults['paypal_client_id']);
        $this->paypal_client_secret = settings('paypal_client_secret', $defaults['paypal_client_secret']);
        $this->paypal_sandbox = (bool) settings('paypal_sandbox', $defaults['paypal_sandbox']);
        $this->webhook_url = settings('webhook_url', $defaults['webhook_url']);
        $this->webhook_secret = settings('webhook_secret', $defaults['webhook_secret']);

        $this->cancellation_policy = settings('cancellation_policy', $defaults['cancellation_policy']);
        $this->deposit_policy = settings('deposit_policy', $defaults['deposit_policy']);
        $this->refund_policy = settings('refund_policy', $defaults['refund_policy']);
        $this->pickup_policy = settings('pickup_policy', $defaults['pickup_policy']);
        $this->additional_terms = settings('additional_terms', $defaults['additional_terms']);
        $this->show_policies_on_storefront = (bool) settings('show_policies_on_storefront', $defaults['show_policies_on_storefront']);

        $eventTypes = settings('catering_event_types');
        $decoded = $eventTypes ? json_decode($eventTypes, true) : null;
        $this->catering_event_types = is_array($decoded) && $decoded !== []
            ? array_values(array_filter($decoded, fn (mixed $v) => is_string($v) && trim($v) !== ''))
            : $defaults['catering_event_types'];

        $this->gift_card_preset_amounts = settings('gift_card_preset_amounts', $defaults['gift_card_preset_amounts']);
        $this->gift_card_default_amount = (int) settings('gift_card_default_amount', $defaults['gift_card_default_amount']);

        $storedSteps = settings('order_journey_steps');
        $decodedSteps = $storedSteps ? json_decode($storedSteps, true) : null;
        $this->order_journey_steps = is_array($decodedSteps) && $decodedSteps !== []
            ? $decodedSteps
            : $defaults['order_journey_steps'];
    }

    public function content(Schema $schema): Schema
    {
        return ManageSettingsForm::configure($schema);
    }

    public function save(): void
    {
        try {
            resolve(SaveTenantSettings::class)($this->toSettingsArray());

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

    public function regenerateWebhookSecret(): void
    {
        $this->webhook_secret = resolve(RegenerateWebhookSecret::class)();

        Notification::make()
            ->title('Webhook secret regenerated')
            ->body('Update any external integrations with the new value.')
            ->success()
            ->send();
    }

    public function sendTestWebhook(): void
    {
        // Persist any pending changes (URL/secret) before firing the test, so
        // the dispatch reads the current form state — not the last-saved state.
        resolve(SaveTenantSettings::class)($this->toSettingsArray());

        resolve(SendTestWebhook::class)();

        Notification::make()
            ->title('Test webhook sent')
            ->body('Check the Webhook Deliveries page to see the response.')
            ->success()
            ->send();
    }

    public function resetToDefaults(): void
    {
        $defaults = TenantSettingsDefaults::all();

        $this->store_name = $defaults['store_name'];
        $this->store_email = $defaults['store_email'];
        $this->store_phone = $defaults['store_phone'];
        $this->store_address = $defaults['store_address'];
        $this->default_daily_capacity = $defaults['default_daily_capacity'];
        $this->minimum_order_lead_hours = $defaults['minimum_order_lead_hours'];
        $defaultTiers = $defaults['delivery_fee_tiers'];
        $decodedDefaultTiers = is_string($defaultTiers) ? json_decode($defaultTiers, true) : $defaultTiers;
        $this->delivery_fee_tiers = is_array($decodedDefaultTiers) ? array_values($decodedDefaultTiers) : [];
        $this->minimum_pickup_order_amount = $defaults['minimum_pickup_order_amount'];
        $this->minimum_delivery_order_amount = $defaults['minimum_delivery_order_amount'];
        $this->repeat_reminders_enabled = $defaults['repeat_reminders_enabled'];
        $this->birthday_program_enabled = $defaults['birthday_program_enabled'];
        $this->email_order_placed_enabled = $defaults['email_order_placed_enabled'];
        $this->email_order_confirmed_enabled = $defaults['email_order_confirmed_enabled'];
        $this->email_order_baking_enabled = $defaults['email_order_baking_enabled'];
        $this->email_order_ready_enabled = $defaults['email_order_ready_enabled'];
        $this->email_order_delivered_enabled = $defaults['email_order_delivered_enabled'];
        $this->email_order_cancelled_enabled = $defaults['email_order_cancelled_enabled'];
        $this->email_order_message_enabled = $defaults['email_order_message_enabled'];
        $this->email_product_available_enabled = $defaults['email_product_available_enabled'];
        $this->allergy_disclaimer = $defaults['allergy_disclaimer'];
        $this->revenue_cap = $defaults['revenue_cap'];
        $this->cancellation_policy = $defaults['cancellation_policy'];
        $this->deposit_policy = $defaults['deposit_policy'];
        $this->refund_policy = $defaults['refund_policy'];
        $this->pickup_policy = $defaults['pickup_policy'];
        $this->additional_terms = $defaults['additional_terms'];
        $this->show_policies_on_storefront = $defaults['show_policies_on_storefront'];
        $this->catering_event_types = $defaults['catering_event_types'];
        $this->gift_card_preset_amounts = $defaults['gift_card_preset_amounts'];
        $this->gift_card_default_amount = $defaults['gift_card_default_amount'];
        $this->order_journey_steps = $defaults['order_journey_steps'];

        Notification::make()
            ->title('Settings reset to defaults')
            ->info()
            ->send();
    }

    /** @return array<string, mixed> */
    private function toSettingsArray(): array
    {
        return [
            'store_name' => $this->store_name,
            'store_email' => $this->store_email,
            'store_phone' => $this->store_phone,
            'store_address' => $this->store_address,
            'default_daily_capacity' => $this->default_daily_capacity,
            'minimum_order_lead_hours' => $this->minimum_order_lead_hours,
            'delivery_fee_tiers' => $this->delivery_fee_tiers,
            'minimum_pickup_order_amount' => $this->minimum_pickup_order_amount,
            'minimum_delivery_order_amount' => $this->minimum_delivery_order_amount,
            'repeat_reminders_enabled' => $this->repeat_reminders_enabled,
            'birthday_program_enabled' => $this->birthday_program_enabled,
            'email_order_placed_enabled' => $this->email_order_placed_enabled,
            'email_order_confirmed_enabled' => $this->email_order_confirmed_enabled,
            'email_order_baking_enabled' => $this->email_order_baking_enabled,
            'email_order_ready_enabled' => $this->email_order_ready_enabled,
            'email_order_delivered_enabled' => $this->email_order_delivered_enabled,
            'email_order_cancelled_enabled' => $this->email_order_cancelled_enabled,
            'email_order_message_enabled' => $this->email_order_message_enabled,
            'email_product_available_enabled' => $this->email_product_available_enabled,
            'allergy_disclaimer' => $this->allergy_disclaimer,
            'revenue_cap' => $this->revenue_cap,
            'payment_methods' => $this->payment_methods,
            'paypal_client_id' => $this->paypal_client_id,
            'paypal_client_secret' => $this->paypal_client_secret,
            'paypal_sandbox' => $this->paypal_sandbox,
            'webhook_url' => $this->webhook_url,
            'webhook_secret' => $this->webhook_secret,
            'cancellation_policy' => $this->cancellation_policy,
            'deposit_policy' => $this->deposit_policy,
            'refund_policy' => $this->refund_policy,
            'pickup_policy' => $this->pickup_policy,
            'additional_terms' => $this->additional_terms,
            'show_policies_on_storefront' => $this->show_policies_on_storefront,
            'catering_event_types' => $this->catering_event_types,
            'gift_card_preset_amounts' => $this->gift_card_preset_amounts,
            'gift_card_default_amount' => $this->gift_card_default_amount,
            'order_journey_steps' => $this->order_journey_steps,
        ];
    }
}
