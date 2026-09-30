<?php

use App\Enums\Orders\DeliveryType;
use App\Filament\Pages\Operations\QuickOrder;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('quick order page can render', function () {
    livewire(QuickOrder::class)
        ->assertOk();
});

test('quick order delivery tier is only shown for delivery', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [['description' => 'Local', 'fee' => 5.00]],
    ])));

    livewire(QuickOrder::class)
        ->fillForm(['delivery_type' => DeliveryType::Pickup->value])
        ->assertSchemaComponentHidden('delivery_tier', 'form')
        ->fillForm(['delivery_type' => DeliveryType::Delivery->value])
        ->assertSchemaComponentVisible('delivery_tier', 'form');
});

test('quick order delivery tier is required for delivery', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [['description' => 'Local', 'fee' => 5.00]],
    ])));

    livewire(QuickOrder::class)
        ->fillForm(['delivery_type' => DeliveryType::Delivery->value])
        ->call('createOrder')
        ->assertHasFormErrors(['delivery_tier' => 'required']);
});

test('quick order delivery tier lists the bakery tiers', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings([
        'deliveryFeeTiers' => [
            ['description' => 'Local', 'fee' => 5.00],
            ['description' => 'Far', 'fee' => 12.50],
        ],
    ])));

    livewire(QuickOrder::class)
        ->fillForm(['delivery_type' => DeliveryType::Delivery->value])
        ->assertFormFieldExists('delivery_tier', fn (Select $field): bool => array_keys($field->getOptions()) === [0, 1]
            && $field->getOptions()[1] === 'Far ($12.50)');
});
