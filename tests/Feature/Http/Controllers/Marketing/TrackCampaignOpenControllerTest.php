<?php

use App\Models\Engagement\CustomerCampaignLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('stamps opened_at when the pixel is hit and returns a GIF', function () {
    $token = (string) Str::ulid();  // exactly 26 chars
    $log = CustomerCampaignLog::factory()->create(['tracking_token' => $token]);

    $response = withoutMiddleware(tenantMiddleware())->get("/track/email-open/{$token}.gif");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/gif');
    expect($log->refresh()->opened_at)->not->toBeNull();
});

test('subsequent hits do not overwrite opened_at (first-hit wins)', function () {
    $token = (string) Str::ulid();
    $log = CustomerCampaignLog::factory()->opened()->create([
        'tracking_token' => $token,
        'opened_at' => now()->subHour(),
    ]);
    $original = $log->refresh()->opened_at ?? throw new LogicException('Expected opened timestamp.');

    withoutMiddleware(tenantMiddleware())->get("/track/email-open/{$token}.gif");

    expect($log->refresh()->opened_at->toIso8601String())->toBe($original->toIso8601String());
});

test('returns the GIF even when the token is unknown', function () {
    $token = (string) Str::ulid();
    $response = withoutMiddleware(tenantMiddleware())->get("/track/email-open/{$token}.gif");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/gif');
});

test('rejects malformed tokens via route regex', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/track/email-open/short.gif');

    $response->assertNotFound();
});
