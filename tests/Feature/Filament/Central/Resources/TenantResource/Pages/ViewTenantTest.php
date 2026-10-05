<?php

use App\Filament\Central\Resources\TenantResource\Pages\ViewTenant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('the branding swatches only print valid hex colors', function () {
    $tenant = Tenant::factory()->create([
        'brand_color_primary' => 'red;}body{display:none',
        'brand_color_secondary' => '#336699',
    ]);

    livewire(ViewTenant::class, ['record' => $tenant->getKey()])
        ->assertOk()
        ->assertSeeHtml('background: #d4920c')
        ->assertSeeHtml('background: #336699')
        ->assertDontSeeHtml('background: red;}body{display:none');
});
