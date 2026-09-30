<?php

use App\Filament\Pages\Operations\QuickOrder;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

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

test('quick order date picker starts at the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    $form = livewire(QuickOrder::class)->instance()->form;
    $minDate = collect($form->getFlatComponents())
        ->first(fn (Component $component): bool => $component instanceof DatePicker && $component->getName() === 'delivery_date')
        ->getMinDate();

    expect(Date::parse($minDate)->toDateString())->toBe('2026-10-05');
});
