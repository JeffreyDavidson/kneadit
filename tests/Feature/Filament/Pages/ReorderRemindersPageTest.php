<?php

use App\Filament\Pages\Operations\ReorderReminders;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('reorder reminders page can render', function () {
    Livewire::test(ReorderReminders::class)
        ->assertOk();
});
