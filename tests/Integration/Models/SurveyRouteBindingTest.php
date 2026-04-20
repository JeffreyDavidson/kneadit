<?php

use App\Models\Engagement\Survey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('resolveRouteBinding throws for inactive survey', function () {
    $survey = Survey::factory()->inactive()->create();

    (new Survey)->resolveRouteBinding($survey->id);
})->throws(ModelNotFoundException::class);

test('resolveRouteBinding returns active survey', function () {
    $survey = Survey::factory()->active()->create();

    $resolved = (new Survey)->resolveRouteBinding($survey->id);

    expect($resolved)->toBeInstanceOf(Survey::class)
        ->and($resolved->id)->toBe($survey->id);
});
