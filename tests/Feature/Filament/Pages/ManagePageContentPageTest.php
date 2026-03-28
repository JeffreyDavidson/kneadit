<?php

use App\Filament\Pages\ManagePageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('manage page content renders and saves', function () {
    Livewire::test(ManagePageContent::class)
        ->assertOk()
        ->call('save');
});
