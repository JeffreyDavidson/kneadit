<?php

use App\Models\Survey;
use App\Models\SurveyResponse;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('survey page loads for active survey', function () {
    $survey = Survey::create([
        'title' => 'Customer Satisfaction',
        'description' => 'Tell us how we did',
        'questions' => [
            ['type' => 'rating', 'question' => 'How was the food?'],
            ['type' => 'text', 'question' => 'Any comments?'],
        ],
        'is_active' => true,
        'responses_count' => 0,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/survey/{$survey->id}");

    $response->assertOk();
    $response->assertSee('Customer Satisfaction');
});

test('survey returns 404 for inactive survey', function () {
    $survey = Survey::create([
        'title' => 'Old Survey',
        'questions' => [['type' => 'text', 'question' => 'Feedback']],
        'is_active' => false,
        'responses_count' => 0,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/survey/{$survey->id}");

    $response->assertNotFound();
});

test('survey response can be submitted', function () {
    $survey = Survey::create([
        'title' => 'Quick Poll',
        'questions' => [['type' => 'rating', 'question' => 'Rate us']],
        'is_active' => true,
        'responses_count' => 0,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post("/survey/{$survey->id}", [
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'answers' => ['4'],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('survey_responses', [
        'survey_id' => $survey->id,
        'customer_name' => 'Alice',
    ]);
});

test('survey response saves answers as json', function () {
    $survey = Survey::create([
        'title' => 'Detailed Survey',
        'questions' => [
            ['type' => 'rating', 'question' => 'Quality'],
            ['type' => 'text', 'question' => 'Comments'],
        ],
        'is_active' => true,
        'responses_count' => 0,
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post("/survey/{$survey->id}", [
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'answers' => ['5', 'Great bread!'],
        ]);

    $surveyResponse = SurveyResponse::first();
    expect($surveyResponse->answers)->toBeArray();
    expect($surveyResponse->answers)->toBe(['5', 'Great bread!']);
});

test('survey response increments responses count', function () {
    $survey = Survey::create([
        'title' => 'Counter Test',
        'questions' => [['type' => 'text', 'question' => 'Feedback']],
        'is_active' => true,
        'responses_count' => 0,
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post("/survey/{$survey->id}", [
            'answers' => ['Nice!'],
        ]);

    expect($survey->fresh()->responses_count)->toBe(1);
});

test('rating questions accept valid values', function () {
    $survey = Survey::create([
        'title' => 'Rating Test',
        'questions' => [['type' => 'rating', 'question' => 'Score']],
        'is_active' => true,
        'responses_count' => 0,
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post("/survey/{$survey->id}", [
            'answers' => ['3'],
        ]);

    $surveyResponse = SurveyResponse::first();
    expect($surveyResponse->answers)->toBe(['3']);
});

test('text questions accept string answers', function () {
    $survey = Survey::create([
        'title' => 'Text Test',
        'questions' => [['type' => 'text', 'question' => 'Thoughts']],
        'is_active' => true,
        'responses_count' => 0,
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post("/survey/{$survey->id}", [
            'answers' => ['I love your croissants!'],
        ]);

    $surveyResponse = SurveyResponse::first();
    expect($surveyResponse->answers)->toBe(['I love your croissants!']);
});
