<?php

use App\Filament\Pages\Platform\Messages;
use App\Models\Staff\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('messages page can render', function () {
    livewire(Messages::class)
        ->assertOk();
});
