<?php

use App\Filament\Pages\Analytics\SurveyResults;
use App\Models\Engagement\Survey;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new SurveyResults;
});

test('survey id defaults to null', function () {
    expect(testFixture('page', SurveyResults::class)->surveyId)->toBeNull();
});

test('get survey property returns null when no survey selected', function () {
    expect(testFixture('page', SurveyResults::class)->getSurveyProperty())->toBeNull();
});

test('get survey property returns survey when selected', function () {
    $survey = Survey::factory()->create(['title' => 'Customer Feedback']);

    testFixture('page', SurveyResults::class)->surveyId = $survey->id;

    $selected = testFixture('page', SurveyResults::class)->getSurveyProperty();
    throw_unless($selected instanceof Survey, RuntimeException::class, 'Expected the selected survey.');

    expect($selected->title)->toBe('Customer Feedback');
});

test('get survey property returns null for nonexistent id', function () {
    testFixture('page', SurveyResults::class)->surveyId = 99999;

    expect(testFixture('page', SurveyResults::class)->getSurveyProperty())->toBeNull();
});

test('get view data returns surveys list', function () {
    Survey::factory()->create(['title' => 'Survey A']);
    Survey::factory()->create(['title' => 'Survey B']);

    $method = new ReflectionMethod(SurveyResults::class, 'getViewData');
    $viewData = $method->invoke(testFixture('page', SurveyResults::class));
    throw_unless(is_array($viewData), RuntimeException::class, 'Expected survey view data.');

    expect($viewData)->toHaveKey('surveys')
        ->and($viewData['surveys'] ?? null)->toHaveCount(2);
});

test('get view data includes current survey as null when none selected', function () {
    $method = new ReflectionMethod(SurveyResults::class, 'getViewData');
    $viewData = $method->invoke(testFixture('page', SurveyResults::class));
    throw_unless(is_array($viewData), RuntimeException::class, 'Expected survey view data.');

    expect($viewData['survey'] ?? null)->toBeNull();
});
