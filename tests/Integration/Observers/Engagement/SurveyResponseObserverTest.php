<?php

use App\Models\Engagement\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creating sets created_at when not provided', function () {
    Illuminate\Support\Facades\Date::setTestNow('2026-04-22 10:00:00');

    $response = SurveyResponse::factory()->create(['created_at' => null]);

    expect($response->refresh()->created_at?->toDateTimeString())->toBe('2026-04-22 10:00:00');

    Illuminate\Support\Facades\Date::setTestNow();
});

test('creating preserves an explicitly-provided created_at', function () {
    $explicit = Illuminate\Support\Facades\Date::parse('2026-01-15 09:00:00');

    $response = SurveyResponse::factory()->create(['created_at' => $explicit]);

    expect($response->refresh()->created_at?->toDateTimeString())->toBe('2026-01-15 09:00:00');
});
