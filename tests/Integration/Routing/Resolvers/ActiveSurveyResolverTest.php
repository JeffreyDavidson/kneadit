<?php

use App\Models\Engagement\Survey;
use App\Routing\Resolvers\ActiveSurveyResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns an active survey by id', function () {
    $survey = Survey::factory()->active()->create();

    $resolved = (new ActiveSurveyResolver)($survey->id);

    expect($resolved)->toBeInstanceOf(Survey::class)
        ->and($resolved->id)->toBe($survey->id);
});

test('throws ModelNotFoundException for inactive survey', function () {
    $survey = Survey::factory()->inactive()->create();

    (new ActiveSurveyResolver)($survey->id);
})->throws(ModelNotFoundException::class);
