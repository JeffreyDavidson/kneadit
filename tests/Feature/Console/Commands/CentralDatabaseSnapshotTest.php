<?php

use App\Actions\Platform\ProcessScheduledCheckins;
use App\Events\Platform\ScheduledCheckinDue;
use App\Models\Operations\CheckinLog;
use App\Models\Platform\Tenant;
use App\Services\Platform\Contracts\DnsResolver;
use App\Services\Tenants\TenancyManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

/*
 * Production's central database is a WAL-mode SQLite file that web, queue and
 * scheduler processes all write to. A read statement left open (a cursor) pins
 * a snapshot, and when another process commits meanwhile, SQLite refuses this
 * connection's next write at once with "database is locked", without waiting
 * for busy_timeout. These tests use a real file and a second connection to
 * stand in for that other process.
 */

beforeEach(function () {
    test()->walDatabasePath = sys_get_temp_dir().'/kneadit_wal_'.getmypid().'_'.Str::random(8).'.sqlite';

    config(['database.connections.sqlite.database' => test()->walDatabasePath]);
    DB::purge('sqlite');
    DB::purge('central');

    setUpCentralOnlyTest();

    test()->otherProcess = new PDO('sqlite:'.test()->walDatabasePath);
    test()->otherProcess->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    test()->otherProcess->exec('PRAGMA busy_timeout = 5000');

    Date::setTestNow('2026-10-05 09:30');
});

afterEach(function () {
    test()->otherProcess = null;
    DB::purge('sqlite');
    DB::purge('central');

    foreach (['', '-wal', '-shm'] as $suffix) {
        if (file_exists(test()->walDatabasePath.$suffix)) {
            unlink(test()->walDatabasePath.$suffix);
        }
    }
});

/** Commits a write from a second connection, the way a scheduled task in another process would. */
function commitFromAnotherProcess(): void
{
    test()->otherProcess->exec("INSERT INTO platform_settings (key, value, created_at, updated_at) VALUES ('probe-".Str::random(6)."', '1', '2026-10-05 09:30:00', '2026-10-05 09:30:00')");
}

test('the test database is a WAL file', function () {
    $mode = DB::connection('central')->selectOne('PRAGMA journal_mode');

    expect($mode->journal_mode)->toBe('wal');
});

test('a write fails at once when another connection committed while a cursor was open', function () {
    createTenant(['id' => 'bakery-a']);
    createTenant(['id' => 'bakery-b']);

    $cursor = DB::table('tenants')->cursor();
    $write = function () use ($cursor): void {
        foreach ($cursor as $tenant) {
            commitFromAnotherProcess();

            DB::table('tenants')->where('id', $tenant->id)->update(['name' => 'Renamed']);
        }
    };

    expect($write)->toThrow(QueryException::class, 'database is locked');
});

test('a write succeeds when another connection committed after the rows were loaded', function () {
    createTenant(['id' => 'bakery-a']);
    createTenant(['id' => 'bakery-b']);

    foreach (DB::table('tenants')->get() as $tenant) {
        commitFromAnotherProcess();

        DB::table('tenants')->where('id', $tenant->id)->update(['name' => 'Renamed']);
    }

    expect(DB::table('tenants')->where('name', 'Renamed')->count())->toBe(2);
});

test('tenants:sync-onboarding-metrics saves every tenant even when another process commits mid-run', function () {
    createTenant(['id' => 'bakery-a']);
    createTenant(['id' => 'bakery-b']);
    $tenancy = Mockery::mock(TenancyManager::class);
    $tenancy->shouldReceive('withinTenant')->andReturnUsing(function (): array {
        commitFromAnotherProcess();

        return [
            'onboarding_products_count' => 3,
            'onboarding_categories_count' => 2,
            'onboarding_orders_count' => 1,
        ];
    });
    app()->instance(TenancyManager::class, $tenancy);

    artisan('tenants:sync-onboarding-metrics')
        ->expectsOutput('Onboarding metrics synced: 2; failed: 0.')
        ->assertSuccessful();

    expect(Tenant::query()->where('onboarding_products_count', 3)->count())->toBe(2);
});

test('tenants:verify-custom-domains saves every tenant even when another process commits mid-run', function () {
    config(['services.forge.server_ip' => '203.0.113.10']);
    createTenant(['id' => 'bakery-a', 'custom_domain' => 'a.example.com']);
    createTenant(['id' => 'bakery-b', 'custom_domain' => 'b.example.com']);
    $resolver = Mockery::mock(DnsResolver::class);
    $resolver->allows('ipv4')->andReturnUsing(function (): string {
        commitFromAnotherProcess();

        return '203.0.113.10';
    });
    app()->instance(DnsResolver::class, $resolver);
    fakeHttpsProbe(['a.example.com' => true, 'b.example.com' => true]);

    artisan('tenants:verify-custom-domains')
        ->expectsOutput('Custom domains checked: 2; verified: 2; unverified: 0; changed: 2.')
        ->assertSuccessful();

    expect(Tenant::query()->whereNotNull('custom_domain_verified_at')->count())->toBe(2);
});

test('scheduled check-ins are logged for every tenant even when another process commits mid-run', function () {
    DB::table('scheduled_checkins')->insert([
        'id' => 10,
        'name' => 'Week 1 Checkin',
        'days_after_signup' => 7,
        'subject' => 'How is it going?',
        'body' => 'Welcome email body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    createTenant(['id' => 'bakery-a', 'email' => 'a@example.com', 'created_at' => now()->subDays(7)->startOfDay()]);
    createTenant(['id' => 'bakery-b', 'email' => 'b@example.com', 'created_at' => now()->subDays(7)->startOfDay()]);
    config(['app.url' => 'http://kneadit.test:8000']);
    Event::fake([ScheduledCheckinDue::class]);
    Event::listen('eloquent.created: '.CheckinLog::class, fn () => commitFromAnotherProcess());

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['sent'])->toBe(2)
        ->and($summary['failures'])->toBe(0)
        ->and(DB::table('checkin_logs')->count())->toBe(2);
});
