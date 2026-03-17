<?php

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

test('questions are stored as json array', function () {
    $questions = [
        ['text' => 'How was the service?', 'type' => 'rating'],
        ['text' => 'Any suggestions?', 'type' => 'text'],
    ];

    $survey = Survey::create([
        'title' => 'Customer Feedback',
        'questions' => $questions,
        'is_active' => true,
    ]);

    $survey->refresh();
    expect($survey->questions)->toBeArray();
    expect($survey->questions)->toHaveCount(2);
    expect($survey->questions[0]['text'])->toBe('How was the service?');
});

test('survey has responses relationship', function () {
    $survey = Survey::create([
        'title' => 'Feedback',
        'questions' => [['text' => 'Rate us', 'type' => 'rating']],
        'is_active' => true,
    ]);

    SurveyResponse::create([
        'survey_id' => $survey->id,
        'answers' => ['5'],
    ]);

    expect($survey->fresh()->responses)->toHaveCount(1);
});

test('is active is cast to boolean', function () {
    $survey = Survey::create([
        'title' => 'Test Survey',
        'questions' => [],
        'is_active' => true,
    ]);

    expect($survey->is_active)->toBeBool();
    expect($survey->is_active)->toBeTrue();
});

test('responses count is cast to integer', function () {
    $survey = Survey::create([
        'title' => 'Test Survey',
        'questions' => [],
        'is_active' => true,
        'responses_count' => 42,
    ]);

    $survey->refresh();
    expect($survey->responses_count)->toBeInt();
    expect($survey->responses_count)->toBe(42);
});
