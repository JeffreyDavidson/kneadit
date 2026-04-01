<?php

use App\Filament\Pages\Operations\StaffManagement;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('staff management page can render', function () {
    Livewire::test(StaffManagement::class)
        ->assertOk();
});
