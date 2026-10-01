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

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Settings';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    protected string $view = 'filament.pages.settings.manage-settings';

    #[\Override]
    protected static ?string $title = 'Manage Settings';

    // Form data properties
    public ?string $store_name = '';

    public ?string $store_email = '';

    public ?string $store_phone = '';

    public ?string $store_address = '';

    public ?string $store_website = '';

    public ?string $store_city = '';

    public ?string $store_state = '';

    public ?string $store_zip = '';

    public ?int $default_daily_capacity = null;

    public ?int $minimum_order_lead_hours = 48;

    public ?string $timezone = 'UTC';

    public ?int $default_shelf_life_days = 3;

    /** @var array<int, array<string, mixed>> */
    public array $delivery_fee_tiers = [];

    public ?string $minimum_pickup_order_amount = '0';

    public ?string $minimum_delivery_order_amount = '0';

    public ?int $order_modification_window_minutes = 0;

    public bool $pickup_slots_enabled = false;

    public ?int $pickup_slot_interval_minutes = 30;

    public ?int $pickup_slot_max_per_window = 3;

    public bool $sitewide_sale_enabled = false;

    public ?int $sitewide_sale_percent = 0;

    public ?string $sitewide_sale_label = 'Sale';

    public bool $repeat_reminders_enabled = false;

    public ?int $repeat_reminder_days = 30;

    public bool $birthday_program_enabled = false;

    public bool $birthday_coupon_enabled = true;

    public ?int $birthday_discount_percentage = 15;

    public ?int $birthday_coupon_valid_days = 7;

    public bool $review_requests_enabled = false;

    public ?int $review_request_delay_hours = 24;

    public bool $weekly_digest_enabled = true;

    public bool $low_stock_alerts_enabled = false;

    public bool $customer_referral_program_enabled = false;

    public ?int $customer_referral_discount_dollars = 10;

    public bool $abandoned_cart_recovery_enabled = false;

    public ?int $abandoned_cart_recovery_hours = 24;

    public ?int $abandoned_cart_recovery_coupon_dollars = 5;

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

    public ?string $paypal_invoice_terms = 'Payment due within 30 days.';

    public bool $paypal_sandbox = true;

    public string $webhook_url = '';

    public string $webhook_secret = '';

    public ?string $cancellation_policy = '';

    public ?string $deposit_policy = '';

    public ?string $refund_policy = '';

    public ?string $pickup_policy = '';

    public ?string $additional_terms = '';

    public bool $show_policies_on_storefront = false;

    public bool $catering_enabled = false;

    public ?int $catering_minimum_guests = 10;

    public ?int $catering_lead_time_days = 14;

    public ?int $catering_deposit_percent = 25;

    /** @var array<int, string> */
    public array $catering_event_types = [];

    public ?string $gift_card_preset_amounts = '';

    public ?int $gift_card_default_amount = 25;

    /** @var array<int, array<string, string>> */
    public array $order_journey_steps = [];

    public function mount(TenantSettingsFormMapper $formMapper): void
    {
        $this->loadSettings($formMapper);
    }

    protected function loadSettings(TenantSettingsFormMapper $formMapper): void
    {
        $defaults = TenantSettingsDefaults::all();
        $values = [];

        foreach ($defaults as $key => $default) {
            $values[$key] = settings($key, $default);
        }

        $this->applySettings($formMapper->fromSettings($values, $defaults));
    }

    #[\Override]
    public function content(Schema $schema): Schema
    {
        return ManageSettingsForm::configure($schema);
    }

    public function save(SaveTenantSettings $saveSettings): void
    {
        // Field rules otherwise only run in the browser; enforce them here so a
        // tampered or stale request cannot persist invalid values.
        $this->validate();

        try {
            $saveSettings($this->toSettingsArray());

            Notification::make()
                ->title('Settings saved successfully!')
                ->success()
                ->send();
        } catch (\Exception) {
            Notification::make()
                ->title('Error saving settings')
                ->body('There was an error saving your settings. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function regenerateWebhookSecret(RegenerateWebhookSecret $regenerateWebhookSecret): void
    {
        $this->webhook_secret = $regenerateWebhookSecret();

        Notification::make()
            ->title('Webhook secret regenerated')
            ->body('Update any external integrations with the new value.')
            ->success()
            ->send();
    }

    public function sendTestWebhook(SaveTenantSettings $saveSettings): void
    {
        // Persist any pending changes (URL/secret) before firing the test, so
        // the dispatch reads the current form state — not the last-saved state.
        $saveSettings($this->toSettingsArray());

        // Resolve after saving because the action's WebhookService snapshots
        // WebhookSettings when it is constructed.
        resolve(SendTestWebhook::class)();

        Notification::make()
            ->title('Test webhook sent')
            ->body('Check the Webhook Deliveries page to see the response.')
            ->success()
            ->send();
    }

    public function resetToDefaults(TenantSettingsFormMapper $formMapper): void
    {
        $defaults = TenantSettingsDefaults::all();
        $this->applySettings($formMapper->fromSettings($defaults, $defaults));

        Notification::make()
            ->title('Settings reset to defaults')
            ->info()
            ->send();
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function applySettings(array $state): void
    {
        foreach ($state as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /** @return array<string, mixed> */
    private function toSettingsArray(): array
    {
        return [
            'store_name' => $this->store_name,
            'store_email' => $this->store_email,
            'store_phone' => $this->store_phone,
            'store_address' => $this->store_address,
            'store_website' => $this->store_website,
            'store_city' => $this->store_city,
            'store_state' => $this->store_state,
            'store_zip' => $this->store_zip,
            'default_daily_capacity' => $this->default_daily_capacity,
            'minimum_order_lead_hours' => $this->minimum_order_lead_hours,
            'timezone' => $this->timezone,
            'default_shelf_life_days' => $this->default_shelf_life_days,
            'delivery_fee_tiers' => $this->delivery_fee_tiers,
            'minimum_pickup_order_amount' => $this->minimum_pickup_order_amount,
            'minimum_delivery_order_amount' => $this->minimum_delivery_order_amount,
            'order_modification_window_minutes' => $this->order_modification_window_minutes,
            'pickup_slots_enabled' => $this->pickup_slots_enabled,
            'pickup_slot_interval_minutes' => $this->pickup_slot_interval_minutes,
            'pickup_slot_max_per_window' => $this->pickup_slot_max_per_window,
            'sitewide_sale_enabled' => $this->sitewide_sale_enabled,
            'sitewide_sale_percent' => $this->sitewide_sale_percent,
            'sitewide_sale_label' => $this->sitewide_sale_label,
            'repeat_reminders_enabled' => $this->repeat_reminders_enabled,
            'repeat_reminder_days' => $this->repeat_reminder_days,
            'birthday_program_enabled' => $this->birthday_program_enabled,
            'birthday_coupon_enabled' => $this->birthday_coupon_enabled,
            'birthday_discount_percentage' => $this->birthday_discount_percentage,
            'birthday_coupon_valid_days' => $this->birthday_coupon_valid_days,
            'review_requests_enabled' => $this->review_requests_enabled,
            'review_request_delay_hours' => $this->review_request_delay_hours,
            'weekly_digest_enabled' => $this->weekly_digest_enabled,
            'low_stock_alerts_enabled' => $this->low_stock_alerts_enabled,
            'customer_referral_program_enabled' => $this->customer_referral_program_enabled,
            'customer_referral_discount_dollars' => $this->customer_referral_discount_dollars,
            'abandoned_cart_recovery_enabled' => $this->abandoned_cart_recovery_enabled,
            'abandoned_cart_recovery_hours' => $this->abandoned_cart_recovery_hours,
            'abandoned_cart_recovery_coupon_dollars' => $this->abandoned_cart_recovery_coupon_dollars,
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
            'paypal_invoice_terms' => $this->paypal_invoice_terms,
            'paypal_sandbox' => $this->paypal_sandbox,
            'webhook_url' => $this->webhook_url,
            'webhook_secret' => $this->webhook_secret,
            'cancellation_policy' => $this->cancellation_policy,
            'deposit_policy' => $this->deposit_policy,
            'refund_policy' => $this->refund_policy,
            'pickup_policy' => $this->pickup_policy,
            'additional_terms' => $this->additional_terms,
            'show_policies_on_storefront' => $this->show_policies_on_storefront,
            'catering_enabled' => $this->catering_enabled,
            'catering_minimum_guests' => $this->catering_minimum_guests,
            'catering_lead_time_days' => $this->catering_lead_time_days,
            'catering_deposit_percent' => $this->catering_deposit_percent,
            'catering_event_types' => $this->catering_event_types,
            'gift_card_preset_amounts' => $this->gift_card_preset_amounts,
            'gift_card_default_amount' => $this->gift_card_default_amount,
            'order_journey_steps' => $this->order_journey_steps,
        ];
    }
}
