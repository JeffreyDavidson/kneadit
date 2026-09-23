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
