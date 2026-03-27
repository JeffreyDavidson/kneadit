<?php

use App\Filament\Resources\Holidays\Pages\ListHolidays;
use App\Models\Holiday;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can create a holiday via slide-over', function () {
    Livewire::test(ListHolidays::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Christmas',
            'date' => '2026-12-25',
            'order_deadline' => '2026-12-20',
            'is_closed' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Holiday::class, [
        'name' => 'Christmas',
    ]);
});
