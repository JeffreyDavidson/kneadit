<?php

use App\Filament\Pages\Operations\QuickOrder;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('quick order page can render', function () {
    Livewire::test(QuickOrder::class)
        ->assertOk();
});
