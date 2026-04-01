<?php

use App\Models\Engagement\Survey;
use App\Models\Engagement\SurveyResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can submit survey response', function () {
    $survey = Survey::factory()->create([
        'is_active' => true,
        'questions' => [
            ['type' => 'rating', 'question' => 'How was your experience?'],
        ],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('survey.submit', ['survey' => $survey], false), [
            'answers' => ['5'],
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
        ]);

    $response->assertRedirect();

    expect(SurveyResponse::query()->count())->toBe(1);
});

test('inactive survey returns 404', function () {
    $survey = Survey::factory()->inactive()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.survey', ['survey' => $survey], false));

    $response->assertNotFound();
});
