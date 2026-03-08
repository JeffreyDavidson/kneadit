<?php

namespace Tests\Unit;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    }

    /** @test */
    public function questions_are_stored_as_json_array(): void
    {
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
        $this->assertIsArray($survey->questions);
        $this->assertCount(2, $survey->questions);
        $this->assertEquals('How was the service?', $survey->questions[0]['text']);
    }

    /** @test */
    public function survey_has_responses_relationship(): void
    {
        $survey = Survey::create([
            'title' => 'Feedback',
            'questions' => [['text' => 'Rate us', 'type' => 'rating']],
            'is_active' => true,
        ]);

        SurveyResponse::create([
            'survey_id' => $survey->id,
            'answers' => ['5'],
        ]);

        $this->assertCount(1, $survey->fresh()->responses);
    }

    /** @test */
    public function is_active_is_cast_to_boolean(): void
    {
        $survey = Survey::create([
            'title' => 'Test Survey',
            'questions' => [],
            'is_active' => true,
        ]);

        $this->assertIsBool($survey->is_active);
        $this->assertTrue($survey->is_active);
    }

    /** @test */
    public function responses_count_is_cast_to_integer(): void
    {
        $survey = Survey::create([
            'title' => 'Test Survey',
            'questions' => [],
            'is_active' => true,
            'responses_count' => 42,
        ]);

        $survey->refresh();
        $this->assertIsInt($survey->responses_count);
        $this->assertEquals(42, $survey->responses_count);
    }
}
