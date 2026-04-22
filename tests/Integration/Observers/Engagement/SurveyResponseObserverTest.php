<?php

use App\Models\Engagement\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creating sets created_at when not provided', function () {
    Carbon::setTestNow('2026-04-22 10:00:00');

    $response = SurveyResponse::factory()->create(['created_at' => null]);

    expect($response->fresh()->created_at?->toDateTimeString())->toBe('2026-04-22 10:00:00');

    Carbon::setTestNow();
});

test('creating preserves an explicitly-provided created_at', function () {
    $explicit = Carbon::parse('2026-01-15 09:00:00');

    $response = SurveyResponse::factory()->create(['created_at' => $explicit]);

    expect($response->fresh()->created_at?->toDateTimeString())->toBe('2026-01-15 09:00:00');
});
