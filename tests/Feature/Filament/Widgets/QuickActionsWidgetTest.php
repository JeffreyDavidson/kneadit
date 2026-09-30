<?php

use App\Filament\Widgets\QuickActionsWidget;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('quick actions widget can render', function () {
    livewire(QuickActionsWidget::class)
        ->assertOk();
});
