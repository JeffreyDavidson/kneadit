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

test('can render waitlist entry table columns', function (string $column) {
    WaitlistEntry::factory()->create();

    Livewire::test(ListWaitlistEntries::class)
        ->assertCanRenderTableColumn($column);
})->with(['customer_name', 'customer_email', 'requested_date', 'status']);

test('can search waitlist entries by customer name', function () {
    $target = WaitlistEntry::factory()->create(['customer_name' => 'Alice Baker']);
    $other = WaitlistEntry::factory()->create(['customer_name' => 'Bob Smith']);

    Livewire::test(ListWaitlistEntries::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});
