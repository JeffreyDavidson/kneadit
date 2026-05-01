<?php

use App\Filament\Central\Resources\FreeForeverGrants\FreeForeverGrantResource;
use App\Filament\Central\Resources\FreeForeverGrants\Pages\ListFreeForeverGrants;
use App\Models\Platform\FreeForeverGrant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('list page renders', function () {
    Livewire::test(ListFreeForeverGrants::class)->assertOk();
});

test('resource cannot create or edit', function () {
    expect(FreeForeverGrantResource::canCreate())->toBeFalse();

    $grant = new FreeForeverGrant;
    expect(FreeForeverGrantResource::canEdit($grant))->toBeFalse();
});

test('list shows active and revoked grants', function () {
    $tenant = Tenant::factory()->create([
        'id' => 'comped-bakery',
        'name' => 'Comped Owner',
        'email' => 'comp@example.com',
        'plan' => 'pro',
        'is_active' => true,
    ]);
    $tenant->domains()->create(['domain' => 'comped-bakery']);

    FreeForeverGrant::factory()->for($tenant)->create([
        'granted_by_user_id' => null,
        'granted_at' => now()->subDays(3),
    ]);
    FreeForeverGrant::factory()->for($tenant)->revoked()->create([
        'granted_by_user_id' => null,
        'granted_at' => now()->subDays(10),
        'revoked_at' => now()->subDays(2),
    ]);

    Livewire::test(ListFreeForeverGrants::class)
        ->assertCanSeeTableRecords(FreeForeverGrant::all());
});
