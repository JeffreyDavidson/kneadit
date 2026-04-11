<?php

use App\Filament\Pages\Analytics\SurveyResults;
use App\Models\Engagement\Survey;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new SurveyResults;
});

test('survey id defaults to null', function () {
    expect(test()->page->surveyId)->toBeNull();
});

test('get survey property returns null when no survey selected', function () {
    expect(test()->page->getSurveyProperty())->toBeNull();
});

test('get survey property returns survey when selected', function () {
    $survey = Survey::factory()->create(['title' => 'Customer Feedback']);

    test()->page->surveyId = $survey->id;

    expect(test()->page->getSurveyProperty())->not->toBeNull()
        ->and(test()->page->getSurveyProperty()->title)->toBe('Customer Feedback');
});

test('get survey property returns null for nonexistent id', function () {
    test()->page->surveyId = 99999;

    expect(test()->page->getSurveyProperty())->toBeNull();
});

test('get view data returns surveys list', function () {
    Survey::factory()->create(['title' => 'Survey A']);
    Survey::factory()->create(['title' => 'Survey B']);

    $method = new ReflectionMethod(SurveyResults::class, 'getViewData');
    $viewData = $method->invoke(test()->page);

    expect($viewData)->toHaveKey('surveys')
        ->and($viewData['surveys'])->toHaveCount(2);
});

test('get view data includes current survey as null when none selected', function () {
    $method = new ReflectionMethod(SurveyResults::class, 'getViewData');
    $viewData = $method->invoke(test()->page);

    expect($viewData['survey'])->toBeNull();
});
