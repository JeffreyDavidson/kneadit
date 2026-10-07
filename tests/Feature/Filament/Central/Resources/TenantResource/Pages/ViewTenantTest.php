<?php

use App\Filament\Central\Resources\TenantResource\Pages\ViewTenant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

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

test('the page never calls a bakery inactive, whatever the retired is_active column holds', function () {
    $tenant = Tenant::factory()->create();
    DB::table('tenants')->update(['is_active' => false]);

    livewire(ViewTenant::class, ['record' => $tenant->getKey()])
        ->assertOk()
        ->assertDontSee('Inactive');
});

test('the page shows a Paused pill for a paused bakery', function () {
    $tenant = Tenant::factory()->create(['paused_at' => now()->subDay()]);

    livewire(ViewTenant::class, ['record' => $tenant->getKey()])
        ->assertSee('Paused');
});
