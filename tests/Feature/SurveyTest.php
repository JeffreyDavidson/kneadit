<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->tenantMiddleware = [
            InitializeTenancyByDomainOrSubdomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureStorefrontEnabled::class,
            TrackPageView::class,
        ];
    }

    /** @test */
    public function survey_page_loads_for_active_survey(): void
    {
        $survey = Survey::create([
            'title' => 'Customer Satisfaction',
            'description' => 'Tell us how we did',
            'questions' => [
                ['type' => 'rating', 'label' => 'How was the food?'],
                ['type' => 'text', 'label' => 'Any comments?'],
            ],
            'is_active' => true,
            'responses_count' => 0,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get("/survey/{$survey->id}");
        $response->assertOk();
        $response->assertSee('Customer Satisfaction');
    }

    /** @test */
    public function survey_returns_404_for_inactive_survey(): void
    {
        $survey = Survey::create([
            'title' => 'Old Survey',
            'questions' => [['type' => 'text', 'label' => 'Feedback']],
            'is_active' => false,
            'responses_count' => 0,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get("/survey/{$survey->id}");
        $response->assertNotFound();
    }

    /** @test */
    public function survey_response_can_be_submitted(): void
    {
        $survey = Survey::create([
            'title' => 'Quick Poll',
            'questions' => [['type' => 'rating', 'label' => 'Rate us']],
            'is_active' => true,
            'responses_count' => 0,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post("/survey/{$survey->id}", [
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'answers' => ['4'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'customer_name' => 'Alice',
        ]);
    }

    /** @test */
    public function survey_response_saves_answers_as_json(): void
    {
        $survey = Survey::create([
            'title' => 'Detailed Survey',
            'questions' => [
                ['type' => 'rating', 'label' => 'Quality'],
                ['type' => 'text', 'label' => 'Comments'],
            ],
            'is_active' => true,
            'responses_count' => 0,
        ]);

        $this->withoutMiddleware($this->tenantMiddleware)->post("/survey/{$survey->id}", [
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'answers' => ['5', 'Great bread!'],
        ]);

        $surveyResponse = SurveyResponse::first();
        $this->assertIsArray($surveyResponse->answers);
        $this->assertEquals(['5', 'Great bread!'], $surveyResponse->answers);
    }

    /** @test */
    public function survey_response_increments_responses_count(): void
    {
        $survey = Survey::create([
            'title' => 'Counter Test',
            'questions' => [['type' => 'text', 'label' => 'Feedback']],
            'is_active' => true,
            'responses_count' => 0,
        ]);

        $this->withoutMiddleware($this->tenantMiddleware)->post("/survey/{$survey->id}", [
            'answers' => ['Nice!'],
        ]);

        $this->assertEquals(1, $survey->fresh()->responses_count);
    }

    /** @test */
    public function rating_questions_accept_valid_values(): void
    {
        $survey = Survey::create([
            'title' => 'Rating Test',
            'questions' => [['type' => 'rating', 'label' => 'Score']],
            'is_active' => true,
            'responses_count' => 0,
        ]);

        $this->withoutMiddleware($this->tenantMiddleware)->post("/survey/{$survey->id}", [
            'answers' => ['3'],
        ]);

        $surveyResponse = SurveyResponse::first();
        $this->assertEquals(['3'], $surveyResponse->answers);
    }

    /** @test */
    public function text_questions_accept_string_answers(): void
    {
        $survey = Survey::create([
            'title' => 'Text Test',
            'questions' => [['type' => 'text', 'label' => 'Thoughts']],
            'is_active' => true,
            'responses_count' => 0,
        ]);

        $this->withoutMiddleware($this->tenantMiddleware)->post("/survey/{$survey->id}", [
            'answers' => ['I love your croissants!'],
        ]);

        $surveyResponse = SurveyResponse::first();
        $this->assertEquals(['I love your croissants!'], $surveyResponse->answers);
    }
}
