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

test('can render survey table columns', function (string $column) {
    Survey::factory()->create();

    Livewire::test(ListSurveys::class)
        ->assertCanRenderTableColumn($column);
})->with(['title']);

test('can search surveys by title', function () {
    $target = Survey::factory()->create(['title' => 'Customer Satisfaction']);
    $other = Survey::factory()->create(['title' => 'Product Feedback']);

    Livewire::test(ListSurveys::class)
        ->searchTable('Satisfaction')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a survey via table action', function () {
    $survey = Survey::factory()->create();

    Livewire::test(ListSurveys::class)
        ->callTableAction('edit', $survey, data: [
            'title' => 'Updated Survey',
        ])
        ->assertHasNoTableActionErrors();

    expect($survey->fresh()->title)->toBe('Updated Survey');
});
