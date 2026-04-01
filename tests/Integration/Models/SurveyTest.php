<?php

use App\Models\Engagement\Survey;
use App\Models\Engagement\SurveyResponse;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('questions are stored as json array', function () {
    $questions = [
        ['text' => 'How was the service?', 'type' => 'rating'],
        ['text' => 'Any suggestions?', 'type' => 'text'],
    ];

    $survey = Survey::factory()->create([
        'title' => 'Customer Feedback',
        'questions' => $questions,
    ]);

    $survey->refresh();
    expect($survey->questions)->toBeArray()->toHaveCount(2)->and($survey->questions[0]['text'])->toBe('How was the service?');
});

test('survey has responses relationship', function () {
    $survey = Survey::factory()->create([
        'title' => 'Feedback',
        'questions' => [['text' => 'Rate us', 'type' => 'rating']],
    ]);

    SurveyResponse::factory()->for($survey)->create();

    expect($survey->fresh()->responses)->toHaveCount(1);
});

test('is active is cast to boolean', function () {
    $survey = Survey::factory()->create([
        'title' => 'Test Survey',
        'questions' => [],
    ]);

    expect($survey->is_active)->toBeBool()->toBeTrue();
});

test('responses count is cast to integer', function () {
    $survey = Survey::factory()->create([
        'title' => 'Test Survey',
        'questions' => [],
        'responses_count' => 42,
    ]);

    $survey->refresh();
    expect($survey->responses_count)->toBeInt()->toBe(42);
});
