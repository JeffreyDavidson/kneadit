<?php

use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can create a setting via slide-over', function () {
    Livewire::test(ListSettings::class)
        ->callAction(CreateAction::class, data: [
            'key' => 'bakery_tagline',
            'value' => 'Fresh bread daily',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Setting::class, [
        'key' => 'bakery_tagline',
    ]);
});
