<?php

use App\Filament\Central\Resources\TenantResource\Pages\ViewTenant;
use App\Filament\Central\Resources\TenantResource\RelationManagers\NotesRelationManager;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('relation manager lists notes attached to the tenant', function () {
    $tenant = createTenant(['id' => 'note-tenant-list', 'email' => 'list@test.com']);
    /** @var Tenant $owner */
    $owner = Tenant::query()->find('note-tenant-list');

    $notes = collect(['First note', 'Second note', 'Third note'])
        ->map(fn (string $body) => TenantNote::factory()->create([
            'tenant_id' => $owner->id,
            'body' => $body,
            'author' => 'Admin',
        ]));

    Livewire::test(NotesRelationManager::class, [
        'ownerRecord' => $owner,
        'pageClass' => ViewTenant::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($notes);
});

test('relation manager renders for a tenant with no notes', function () {
    createTenant(['id' => 'note-tenant-empty', 'email' => 'empty@test.com']);
    /** @var Tenant $owner */
    $owner = Tenant::query()->find('note-tenant-empty');

    Livewire::test(NotesRelationManager::class, [
        'ownerRecord' => $owner,
        'pageClass' => ViewTenant::class,
    ])
        ->assertSuccessful();
});
