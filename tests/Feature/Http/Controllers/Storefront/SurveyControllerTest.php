<?php

use App\Models\Engagement\Survey;
use App\Models\Engagement\SurveyResponse;

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('survey controller passes settings and content to view', function () {
    $survey = Survey::factory()->active()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.survey', $survey, false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('survey page loads for active survey', function () {
    $survey = Survey::factory()->create([
        'title' => 'Customer Satisfaction',
        'description' => 'Tell us how we did',
        'questions' => [
            ['type' => 'rating', 'question' => 'How was the food?'],
            ['type' => 'text', 'question' => 'Any comments?'],
        ],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.survey', $survey, false));

    $response->assertOk();
    $response->assertSee('Customer Satisfaction');
});

test('survey returns 404 for inactive survey', function () {
    $survey = Survey::factory()->inactive()->create([
        'questions' => [['type' => 'text', 'question' => 'Feedback']],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.survey', $survey, false));

    $response->assertNotFound();
});

test('survey response can be submitted', function () {
    $survey = Survey::factory()->create([
        'questions' => [['type' => 'rating', 'question' => 'Rate us']],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('survey.submit', $survey, false), [
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'answers' => ['4'],
        ]);

    $response->assertRedirect();
    test()->assertDatabaseHas('survey_responses', [
        'survey_id' => $survey->id,
        'customer_name' => 'Alice',
    ]);
});

test('survey response saves answers as json', function () {
    $survey = Survey::factory()->create([
        'questions' => [
            ['type' => 'rating', 'question' => 'Quality'],
            ['type' => 'text', 'question' => 'Comments'],
        ],
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post(route('survey.submit', $survey, false), [
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'answers' => ['5', 'Great bread!'],
        ]);

    $surveyResponse = SurveyResponse::query()->firstOrFail();
    expect($surveyResponse->getAttribute('answers'))->toBeArray()->toBe(['5', 'Great bread!']);
});

test('survey response increments responses count', function () {
    $survey = Survey::factory()->create([
        'questions' => [['type' => 'text', 'question' => 'Feedback']],
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post(route('survey.submit', $survey, false), [
            'answers' => ['Nice!'],
        ]);

    expect($survey->refresh()->responses_count)->toBe(1);
});

test('rating questions accept valid values', function () {
    $survey = Survey::factory()->create([
        'questions' => [['type' => 'rating', 'question' => 'Score']],
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post(route('survey.submit', $survey, false), [
            'answers' => ['3'],
        ]);

    $surveyResponse = SurveyResponse::query()->firstOrFail();
    expect($surveyResponse->answers)->toBe(['3']);
});

test('text questions accept string answers', function () {
    $survey = Survey::factory()->create([
        'questions' => [['type' => 'text', 'question' => 'Thoughts']],
    ]);

    withoutMiddleware(tenantMiddleware())
        ->post(route('survey.submit', $survey, false), [
            'answers' => ['I love your croissants!'],
        ]);

    $surveyResponse = SurveyResponse::query()->firstOrFail();
    expect($surveyResponse->answers)->toBe(['I love your croissants!']);
});
