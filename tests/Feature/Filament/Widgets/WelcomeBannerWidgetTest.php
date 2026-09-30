<?php

use App\Filament\Widgets\WelcomeBannerWidget;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('welcome banner widget can render', function () {
    livewire(WelcomeBannerWidget::class)
        ->assertOk();
});
