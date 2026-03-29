<?php

use App\Actions\Customers\SubmitSurveyResponse;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates survey response and increments count', function () {
    $survey = Survey::factory()->create(['responses_count' => 0]);

    $response = resolve(SubmitSurveyResponse::class)(
        $survey,
        ['Great bread', 5],
        'Alice',
        'alice@example.com'
    );

    expect($response)->toBeInstanceOf(SurveyResponse::class)
        ->and($response->survey_id)->toBe($survey->id)
        ->and($response->customer_name)->toBe('Alice')
        ->and($survey->fresh()->responses_count)->toBe(1);
});
