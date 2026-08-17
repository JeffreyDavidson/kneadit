<?php

use App\Services\Analytics\VisitorIdentifier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('visitor identifiers are deterministic keyed pseudonyms', function () {
    config(['app.key' => 'base64:test-analytics-key']);
    $identifier = resolve(VisitorIdentifier::class);

    $first = $identifier->fromSessionId('raw-session-id');
    $second = $identifier->fromSessionId('raw-session-id');

    config(['app.key' => 'base64:rotated-analytics-key']);

    expect($first)
        ->toBe($second)
        ->not->toBe('raw-session-id')
        ->toHaveLength(64)
        ->and($identifier->fromSessionId('raw-session-id'))->not->toBe($first);
});

test('privacy migration pseudonymizes historical sessions and removes raw metadata', function () {
    config(['app.key' => 'base64:test-analytics-key']);

    Schema::table('page_views', function (Blueprint $table) {
        $table->string('ip_address')->nullable();
        $table->string('user_agent')->nullable();
    });

    $id = DB::table('page_views')->insertGetId([
        'page' => 'home',
        'session_id' => 'legacy-raw-session',
        'ip_address' => '203.0.113.1',
        'user_agent' => 'Legacy Browser',
        'created_at' => now(),
    ]);

    $migration = require database_path('migrations/tenant/2026_08_17_030000_minimize_page_view_analytics_data.php');
    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();

    expect(DB::table('page_views')->where('id', $id)->value('session_id'))
        ->toBe(resolve(VisitorIdentifier::class)->fromSessionId('legacy-raw-session'))
        ->and(Schema::hasColumn('page_views', 'ip_address'))->toBeFalse()
        ->and(Schema::hasColumn('page_views', 'user_agent'))->toBeFalse();
});
