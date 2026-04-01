<?php

use App\Filament\Pages\Settings\HomepageBuilder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('homepage builder renders and saves', function () {
    Livewire::test(HomepageBuilder::class)
        ->assertOk()
        ->call('save');

    expect(settings('homepage_sections'))->not->toBeNull();
});
