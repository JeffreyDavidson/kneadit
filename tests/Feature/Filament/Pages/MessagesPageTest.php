<?php

use App\Filament\Pages\Platform\Messages;
use App\Models\Staff\User;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('messages page can render', function () {
    Livewire::test(Messages::class)
        ->assertOk();
});
