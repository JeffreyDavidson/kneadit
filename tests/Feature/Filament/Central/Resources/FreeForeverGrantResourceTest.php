<?php

use App\Filament\Central\Resources\FreeForeverGrants\FreeForeverGrantResource;
use App\Filament\Central\Resources\FreeForeverGrants\Pages\ListFreeForeverGrants;
use App\Models\Platform\FreeForeverGrant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('list page renders', function () {
    livewire(ListFreeForeverGrants::class)->assertOk();
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

    livewire(ListFreeForeverGrants::class)
        ->assertCanSeeTableRecords(FreeForeverGrant::all());
});

test('revoking a grant escapes the bakery name in the notification', function () {
    $name = '<a href="https://evil.example">Sign in</a>';
    $tenant = Tenant::factory()->create(['store_name' => $name]);
    $grant = FreeForeverGrant::factory()->for($tenant)->create(['granted_by_user_id' => null]);

    livewire(ListFreeForeverGrants::class)
        ->callAction(TestAction::make('revoke')->table($grant))
        ->assertNotified(
            Notification::make()
                ->title('Grant revoked')
                ->body('Tenant '.e($name).' is no longer free forever.')
                ->success(),
        );
});
