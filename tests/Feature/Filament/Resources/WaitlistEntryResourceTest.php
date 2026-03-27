<?php

use App\Filament\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
use App\Models\User;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can list waitlist entries in the table', function () {
    $entries = WaitlistEntry::factory()->count(3)->create();

    Livewire::test(ListWaitlistEntries::class)
        ->assertCanSeeTableRecords($entries);
});
