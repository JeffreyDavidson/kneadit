<?php

use App\Filament\Resources\Surveys\Pages\ListSurveys;
use App\Models\Engagement\Survey;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list surveys in the table', function () {
    $surveys = Survey::factory()->count(3)->create();

    livewire(ListSurveys::class)
        ->assertCanSeeTableRecords($surveys);
});

test('can render survey table columns', function () {
    Survey::factory()->create();

    livewire(ListSurveys::class)
        ->assertCanRenderTableColumn('title');
});

test('can search surveys by title', function () {
    $target = Survey::factory()->create(['title' => 'Customer Satisfaction']);
    $other = Survey::factory()->create(['title' => 'Product Feedback']);

    livewire(ListSurveys::class)
        ->searchTable('Satisfaction')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a survey via table action', function () {
    $survey = Survey::factory()->create();

    livewire(ListSurveys::class)
        ->callAction(TestAction::make('edit')->table($survey), data: [
            'title' => 'Updated Survey',
        ])
        ->assertHasNoFormErrors();

    expect($survey->fresh()->title)->toBe('Updated Survey');
});

test('can create a survey with questions repeater', function () {
    $component = livewire(ListSurveys::class)
        ->mountAction('create')
        ->fillForm([
            'title' => 'Customer Satisfaction',
            'description' => 'How did we do?',
            'is_active' => true,
        ]);

    $state = $component->get('mountedActions.0.data.questions');
    $keys = array_keys($state);
    $component->fillForm([
        'questions' => [
            $keys[0] => ['type' => 'rating', 'question' => 'How was your experience?'],
        ],
    ]);

    $component->callMountedAction()
        ->assertHasNoFormErrors();

    $survey = Survey::query()->first();
    expect($survey)
        ->title->toBe('Customer Satisfaction')
        ->questions->toHaveCount(1)->and($survey->questions[0]['question'])->toBe('How was your experience?');
});

test('can filter surveys by active status', function () {
    $active = Survey::factory()->active()->create();
    $inactive = Survey::factory()->inactive()->create();

    livewire(ListSurveys::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords(collect([$active]))
        ->assertCanNotSeeTableRecords(collect([$inactive]));
});

test('edit survey validates title is required', function () {
    $survey = Survey::factory()->create();

    livewire(ListSurveys::class)
        ->callAction(TestAction::make('edit')->table($survey), data: [
            'title' => null,
        ])
        ->assertHasFormErrors(['title' => 'required']);
});

test('can render the view survey page', function () {
    $survey = Survey::factory()->create();

    livewire(App\Filament\Resources\Surveys\Pages\ViewSurvey::class, ['record' => $survey->getRouteKey()])
        ->assertOk();
});

test('can sort surveys by title', function () {
    $alpha = Survey::factory()->create(['title' => 'Alpha Survey']);
    $zeta = Survey::factory()->create(['title' => 'Zeta Survey']);

    livewire(ListSurveys::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('title', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Surveys\SurveyResource::getGloballySearchableAttributes())
        ->toBe(['title']);
});

test('resource returns global search result title', function () {
    $survey = Survey::factory()->create(['title' => 'Customer Feedback']);

    expect(App\Filament\Resources\Surveys\SurveyResource::getGlobalSearchResultTitle($survey))
        ->toBe('Customer Feedback');
});

test('resource returns global search result details', function () {
    $survey = Survey::factory()->create([
        'is_active' => true,
        'responses_count' => 15,
    ]);

    $details = App\Filament\Resources\Surveys\SurveyResource::getGlobalSearchResultDetails($survey);

    expect($details)
        ->toHaveKey('Status', 'Active')
        ->toHaveKey('Responses', '15');
});
