<?php

use App\Filament\Pages\Tools\LabelGenerator;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('label generator shows preview after selecting products', function () {
    $product = Product::factory()->create();

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSet('showPreview', true);
});

test('label generator suggests a best by date counted from the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    $component = livewire(LabelGenerator::class);

    $component->assertSet('bestByDate', '2026-10-08');
});
