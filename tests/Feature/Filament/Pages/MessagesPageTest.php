<?php

use App\Filament\Pages\Messages;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('messages page can render', function () {
    Livewire::test(Messages::class)
        ->assertOk();
});
