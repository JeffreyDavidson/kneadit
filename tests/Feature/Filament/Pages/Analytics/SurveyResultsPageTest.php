<?php

use App\Filament\Pages\Analytics\SurveyResults;
use App\Models\Engagement\Survey;
use App\Models\Engagement\SurveyResponse;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('renders precomputed rating, choice, and text results', function () {
    $survey = Survey::factory()->create([
        'questions' => [
            ['question' => 'How was your visit?', 'type' => 'rating'],
            ['question' => 'Favorite flavor?', 'type' => 'multiple_choice'],
            ['question' => 'Any comments?', 'type' => 'text'],
        ],
    ]);

    SurveyResponse::factory()->for($survey)->create(['answers' => [5, 'Sprinkles', 'Excellent']]);
    SurveyResponse::factory()->for($survey)->create(['answers' => [3, 'Chocolate', 'Good']]);
    SurveyResponse::factory()->for($survey)->create(['answers' => [5, 'Sprinkles', null]]);

    Livewire::test(SurveyResults::class)
        ->set('surveyId', $survey->id)
        ->assertOk()
        ->assertSee('How was your visit?')
        ->assertSee('4.3 / 5')
        ->assertSee('(3 ratings)')
        ->assertSee('Favorite flavor?')
        ->assertSee('Sprinkles')
        ->assertSee('67% (2)')
        ->assertSee('Chocolate')
        ->assertSee('33% (1)')
        ->assertSee('Any comments?')
        ->assertSee('Excellent')
        ->assertSee('Good');
});

test('shows the empty-state message when the survey has no responses', function () {
    $survey = Survey::factory()->create();

    Livewire::test(SurveyResults::class)
        ->set('surveyId', $survey->id)
        ->assertOk()
        ->assertSee('0 responses')
        ->assertSee('No responses yet.');
});
