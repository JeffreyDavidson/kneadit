<?php

use App\Filament\Pages\Settings\ManageEmailTemplates;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('manage email templates page renders for manager', function () {
    Livewire::test(ManageEmailTemplates::class)->assertOk();
});
