<?php

use App\Filament\Resources\Surveys\Pages\ListSurveys;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('can list surveys in the table', function () {
    $surveys = Survey::factory()->count(3)->create();

    Livewire::test(ListSurveys::class)
        ->assertCanSeeTableRecords($surveys);
});
