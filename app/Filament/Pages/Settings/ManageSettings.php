<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Operations\RegenerateWebhookSecret;
use App\Actions\Operations\SendTestWebhook;
use App\Actions\Tenants\SaveTenantSettings;
use App\DataTransferObjects\Settings\SettingValue;
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
        $values = [];

        foreach ($defaults as $key => $default) {
            $values[$key] = settings($key, $default);
        }

        $this->applySettings($values, $defaults);
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
        $this->applySettings($defaults, $defaults);

        Notification::make()
            ->title('Settings reset to defaults')
            ->info()
            ->send();
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, mixed> $defaults
     */
    private function applySettings(array $values, array $defaults): void
    {
        $this->store_name = SettingValue::string($values['store_name'] ?? null, SettingValue::string($defaults['store_name'] ?? null));
        $this->store_email = SettingValue::string($values['store_email'] ?? null, SettingValue::string($defaults['store_email'] ?? null));
        $this->store_phone = SettingValue::string($values['store_phone'] ?? null, SettingValue::string($defaults['store_phone'] ?? null));
        $this->store_address = SettingValue::string($values['store_address'] ?? null, SettingValue::string($defaults['store_address'] ?? null));
        $this->default_daily_capacity = SettingValue::nullableInt($values['default_daily_capacity'] ?? null, SettingValue::nullableInt($defaults['default_daily_capacity'] ?? null));
        $this->minimum_order_lead_hours = SettingValue::nullableInt($values['minimum_order_lead_hours'] ?? null, SettingValue::nullableInt($defaults['minimum_order_lead_hours'] ?? null, 48));
        $this->delivery_fee_tiers = SettingValue::mapList($values['delivery_fee_tiers'] ?? null);
        $this->minimum_pickup_order_amount = SettingValue::string($values['minimum_pickup_order_amount'] ?? null, SettingValue::string($defaults['minimum_pickup_order_amount'] ?? null, '0'));
        $this->minimum_delivery_order_amount = SettingValue::string($values['minimum_delivery_order_amount'] ?? null, SettingValue::string($defaults['minimum_delivery_order_amount'] ?? null, '0'));
        $this->repeat_reminders_enabled = SettingValue::bool($values['repeat_reminders_enabled'] ?? null, SettingValue::bool($defaults['repeat_reminders_enabled'] ?? null));
        $this->birthday_program_enabled = SettingValue::bool($values['birthday_program_enabled'] ?? null, SettingValue::bool($defaults['birthday_program_enabled'] ?? null));
        $this->email_order_placed_enabled = SettingValue::bool($values['email_order_placed_enabled'] ?? null, true);
        $this->email_order_confirmed_enabled = SettingValue::bool($values['email_order_confirmed_enabled'] ?? null, true);
        $this->email_order_baking_enabled = SettingValue::bool($values['email_order_baking_enabled'] ?? null, true);
        $this->email_order_ready_enabled = SettingValue::bool($values['email_order_ready_enabled'] ?? null, true);
        $this->email_order_delivered_enabled = SettingValue::bool($values['email_order_delivered_enabled'] ?? null, true);
        $this->email_order_cancelled_enabled = SettingValue::bool($values['email_order_cancelled_enabled'] ?? null, true);
        $this->email_order_message_enabled = SettingValue::bool($values['email_order_message_enabled'] ?? null, true);
        $this->email_product_available_enabled = SettingValue::bool($values['email_product_available_enabled'] ?? null, true);
        $this->allergy_disclaimer = SettingValue::string($values['allergy_disclaimer'] ?? null, SettingValue::string($defaults['allergy_disclaimer'] ?? null));
        $this->revenue_cap = SettingValue::string($values['revenue_cap'] ?? null, SettingValue::string($defaults['revenue_cap'] ?? null, '250000'));
        $this->payment_methods = $this->withListFallback(SettingValue::stringList($values['payment_methods'] ?? null), SettingValue::stringList($defaults['payment_methods'] ?? [PaymentMethod::Cash->value]));
        $this->paypal_client_id = SettingValue::string($values['paypal_client_id'] ?? null, SettingValue::string($defaults['paypal_client_id'] ?? null));
        $this->paypal_client_secret = SettingValue::string($values['paypal_client_secret'] ?? null, SettingValue::string($defaults['paypal_client_secret'] ?? null));
        $this->paypal_sandbox = SettingValue::bool($values['paypal_sandbox'] ?? null, true);
        $this->webhook_url = SettingValue::string($values['webhook_url'] ?? null, SettingValue::string($defaults['webhook_url'] ?? null));
        $this->webhook_secret = SettingValue::string($values['webhook_secret'] ?? null, SettingValue::string($defaults['webhook_secret'] ?? null));
        $this->cancellation_policy = SettingValue::string($values['cancellation_policy'] ?? null, SettingValue::string($defaults['cancellation_policy'] ?? null));
        $this->deposit_policy = SettingValue::string($values['deposit_policy'] ?? null, SettingValue::string($defaults['deposit_policy'] ?? null));
        $this->refund_policy = SettingValue::string($values['refund_policy'] ?? null, SettingValue::string($defaults['refund_policy'] ?? null));
        $this->pickup_policy = SettingValue::string($values['pickup_policy'] ?? null, SettingValue::string($defaults['pickup_policy'] ?? null));
        $this->additional_terms = SettingValue::string($values['additional_terms'] ?? null, SettingValue::string($defaults['additional_terms'] ?? null));
        $this->show_policies_on_storefront = SettingValue::bool($values['show_policies_on_storefront'] ?? null, SettingValue::bool($defaults['show_policies_on_storefront'] ?? null));
        $this->catering_event_types = $this->withListFallback(SettingValue::stringList($values['catering_event_types'] ?? null), SettingValue::stringList($defaults['catering_event_types'] ?? []));
        $this->gift_card_preset_amounts = SettingValue::string($values['gift_card_preset_amounts'] ?? null, SettingValue::string($defaults['gift_card_preset_amounts'] ?? null));
        $this->gift_card_default_amount = SettingValue::nullableInt($values['gift_card_default_amount'] ?? null, SettingValue::nullableInt($defaults['gift_card_default_amount'] ?? null, 25));

        $journeySteps = SettingValue::stringMapList($values['order_journey_steps'] ?? null);
        $this->order_journey_steps = $journeySteps !== []
            ? $journeySteps
            : SettingValue::stringMapList($defaults['order_journey_steps'] ?? []);
    }

    /**
     * @param list<string> $values
     * @param list<string> $defaults
     * @return list<string>
     */
    private function withListFallback(array $values, array $defaults): array
    {
        return $values !== [] ? $values : $defaults;
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
