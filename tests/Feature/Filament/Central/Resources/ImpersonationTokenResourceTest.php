<?php

use App\Filament\Central\Resources\ImpersonationTokens\ImpersonationTokenResource;
use App\Filament\Central\Resources\ImpersonationTokens\Pages\ListImpersonationTokens;
use App\Models\Platform\ImpersonationToken;
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
    Livewire::test(ListImpersonationTokens::class)->assertOk();
});

test('resource is read-only', function () {
    expect(ImpersonationTokenResource::canCreate())->toBeFalse();

    $token = new ImpersonationToken;
    expect(ImpersonationTokenResource::canEdit($token))->toBeFalse()
        ->and(ImpersonationTokenResource::canDelete($token))->toBeFalse();
});

test('list shows pending, consumed, and expired tokens', function () {
    $tenant = Tenant::query()->create([
        'id' => 'audit-bakery',
        'name' => 'Audit Owner',
        'email' => 'audit@example.com',
        'plan' => 'pro',
        'is_active' => true,
    ]);
    $tenant->domains()->create(['domain' => 'audit-bakery']);

    ImpersonationToken::factory()->for($tenant)->create();
    ImpersonationToken::factory()->for($tenant)->consumed()->create();
    ImpersonationToken::factory()->for($tenant)->expired()->create();

    Livewire::test(ListImpersonationTokens::class)
        ->assertCanSeeTableRecords(ImpersonationToken::all());
});
