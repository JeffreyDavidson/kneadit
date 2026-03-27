<?php

use App\Filament\Pages\QuickOrder;
use App\Models\User;
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
