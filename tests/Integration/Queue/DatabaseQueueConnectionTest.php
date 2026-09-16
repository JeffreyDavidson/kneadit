<?php

use App\Models\Platform\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

beforeEach(fn () => setUpCentralTest());

final class CentralDatabaseQueueProbe implements ShouldQueue {}

test('database queue remains central while tenancy is initialized', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    expect(DB::getDefaultConnection())->toBe('tenant')
        ->and(Config::string('queue.connections.database.connection'))->toBe('central')
        ->and(Config::boolean('queue.connections.database.after_commit'))->toBeTrue()
        ->and(Schema::connection('tenant')->hasTable('jobs'))->toBeFalse()
        ->and(DB::connection('central')->table('jobs')->count())->toBe(0);

    Queue::connection('database')->push(new CentralDatabaseQueueProbe);

    expect(DB::connection('central')->table('jobs')->count())->toBe(1);
});

test('database queue dispatches after a tenant transaction commits', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);
    $tenantConnection = DB::connection('tenant');

    $tenantConnection->beginTransaction();
    Queue::connection('database')->push(new CentralDatabaseQueueProbe);

    expect(DB::connection('central')->table('jobs')->count())->toBe(0);

    $tenantConnection->commit();

    expect(DB::connection('central')->table('jobs')->count())->toBe(1);
});

test('database queue discards dispatches when a tenant transaction rolls back', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);
    $tenantConnection = DB::connection('tenant');

    $tenantConnection->beginTransaction();
    Queue::connection('database')->push(new CentralDatabaseQueueProbe);

    expect(DB::connection('central')->table('jobs')->count())->toBe(0);

    $tenantConnection->rollBack();

    expect(DB::connection('central')->table('jobs')->count())->toBe(0);
});
