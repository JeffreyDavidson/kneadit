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
        $this->store_name = $this->stringValue($values['store_name'] ?? null, $defaults['store_name'] ?? '');
        $this->store_email = $this->stringValue($values['store_email'] ?? null, $defaults['store_email'] ?? '');
        $this->store_phone = $this->stringValue($values['store_phone'] ?? null, $defaults['store_phone'] ?? '');
        $this->store_address = $this->stringValue($values['store_address'] ?? null, $defaults['store_address'] ?? '');
        $this->default_daily_capacity = $this->nullableIntValue($values['default_daily_capacity'] ?? null, $defaults['default_daily_capacity'] ?? null);
        $this->minimum_order_lead_hours = $this->nullableIntValue($values['minimum_order_lead_hours'] ?? null, $defaults['minimum_order_lead_hours'] ?? 48);
        $this->delivery_fee_tiers = $this->mapListValue($values['delivery_fee_tiers'] ?? null);
        $this->minimum_pickup_order_amount = $this->stringValue($values['minimum_pickup_order_amount'] ?? null, $defaults['minimum_pickup_order_amount'] ?? '0');
        $this->minimum_delivery_order_amount = $this->stringValue($values['minimum_delivery_order_amount'] ?? null, $defaults['minimum_delivery_order_amount'] ?? '0');
        $this->repeat_reminders_enabled = $this->boolValue($values['repeat_reminders_enabled'] ?? null, $this->boolValue($defaults['repeat_reminders_enabled'] ?? null));
        $this->birthday_program_enabled = $this->boolValue($values['birthday_program_enabled'] ?? null, $this->boolValue($defaults['birthday_program_enabled'] ?? null));
        $this->email_order_placed_enabled = $this->boolValue($values['email_order_placed_enabled'] ?? null, true);
        $this->email_order_confirmed_enabled = $this->boolValue($values['email_order_confirmed_enabled'] ?? null, true);
        $this->email_order_baking_enabled = $this->boolValue($values['email_order_baking_enabled'] ?? null, true);
        $this->email_order_ready_enabled = $this->boolValue($values['email_order_ready_enabled'] ?? null, true);
        $this->email_order_delivered_enabled = $this->boolValue($values['email_order_delivered_enabled'] ?? null, true);
        $this->email_order_cancelled_enabled = $this->boolValue($values['email_order_cancelled_enabled'] ?? null, true);
        $this->email_order_message_enabled = $this->boolValue($values['email_order_message_enabled'] ?? null, true);
        $this->email_product_available_enabled = $this->boolValue($values['email_product_available_enabled'] ?? null, true);
        $this->allergy_disclaimer = $this->stringValue($values['allergy_disclaimer'] ?? null, $defaults['allergy_disclaimer'] ?? '');
        $this->revenue_cap = $this->stringValue($values['revenue_cap'] ?? null, $defaults['revenue_cap'] ?? '250000');
        $this->payment_methods = $this->stringListValue($values['payment_methods'] ?? null, $defaults['payment_methods'] ?? [PaymentMethod::Cash->value]);
        $this->paypal_client_id = $this->stringValue($values['paypal_client_id'] ?? null, $defaults['paypal_client_id'] ?? '');
        $this->paypal_client_secret = $this->stringValue($values['paypal_client_secret'] ?? null, $defaults['paypal_client_secret'] ?? '');
        $this->paypal_sandbox = $this->boolValue($values['paypal_sandbox'] ?? null, true);
        $this->webhook_url = $this->stringValue($values['webhook_url'] ?? null, $defaults['webhook_url'] ?? '');
        $this->webhook_secret = $this->stringValue($values['webhook_secret'] ?? null, $defaults['webhook_secret'] ?? '');
        $this->cancellation_policy = $this->stringValue($values['cancellation_policy'] ?? null, $defaults['cancellation_policy'] ?? '');
        $this->deposit_policy = $this->stringValue($values['deposit_policy'] ?? null, $defaults['deposit_policy'] ?? '');
        $this->refund_policy = $this->stringValue($values['refund_policy'] ?? null, $defaults['refund_policy'] ?? '');
        $this->pickup_policy = $this->stringValue($values['pickup_policy'] ?? null, $defaults['pickup_policy'] ?? '');
        $this->additional_terms = $this->stringValue($values['additional_terms'] ?? null, $defaults['additional_terms'] ?? '');
        $this->show_policies_on_storefront = $this->boolValue($values['show_policies_on_storefront'] ?? null, $this->boolValue($defaults['show_policies_on_storefront'] ?? null));
        $this->catering_event_types = $this->stringListValue($values['catering_event_types'] ?? null, $defaults['catering_event_types'] ?? []);
        $this->gift_card_preset_amounts = $this->stringValue($values['gift_card_preset_amounts'] ?? null, $defaults['gift_card_preset_amounts'] ?? '');
        $this->gift_card_default_amount = $this->nullableIntValue($values['gift_card_default_amount'] ?? null, $defaults['gift_card_default_amount'] ?? 25);
        $this->order_journey_steps = $this->stringMapListValue($values['order_journey_steps'] ?? null, $defaults['order_journey_steps'] ?? []);
    }

    private function stringValue(mixed $value, mixed $default): string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_string($default) ? $default : '';
    }

    private function nullableIntValue(mixed $value, mixed $default): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_INT);

            if (is_int($filtered)) {
                return $filtered;
            }
        }

        return is_int($default) ? $default : null;
    }

    private function boolValue(mixed $value, bool $default = false): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return is_bool($filtered) ? $filtered : $default;
    }

    /** @return list<array<string, mixed>> */
    private function mapListValue(mixed $value): array
    {
        $items = $this->decodedListValue($value);
        $maps = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $map = [];

            foreach ($item as $key => $mapValue) {
                if (is_string($key)) {
                    $map[$key] = $mapValue;
                }
            }

            $maps[] = $map;
        }

        return $maps;
    }

    /** @return list<string> */
    private function stringListValue(mixed $value, mixed $default): array
    {
        $strings = array_values(array_filter(
            $this->decodedListValue($value),
            fn (mixed $item): bool => is_string($item) && trim($item) !== '',
        ));

        if ($strings !== []) {
            return $strings;
        }

        return array_values(array_filter(
            is_array($default) ? $default : [],
            fn (mixed $item): bool => is_string($item) && trim($item) !== '',
        ));
    }

    /** @return list<array<string, string>> */
    private function stringMapListValue(mixed $value, mixed $default): array
    {
        $maps = $this->stringMaps($this->decodedListValue($value));

        if ($maps !== []) {
            return $maps;
        }

        return $this->stringMaps(is_array($default) ? array_values($default) : []);
    }

    /**
     * @param list<mixed> $items
     * @return list<array<string, string>>
     */
    private function stringMaps(array $items): array
    {
        $maps = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $map = [];

            foreach ($item as $key => $mapValue) {
                if (is_string($key) && is_string($mapValue)) {
                    $map[$key] = $mapValue;
                }
            }

            if ($map !== []) {
                $maps[] = $map;
            }
        }

        return $maps;
    }

    /** @return list<mixed> */
    private function decodedListValue(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) && array_is_list($value) ? $value : [];
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
