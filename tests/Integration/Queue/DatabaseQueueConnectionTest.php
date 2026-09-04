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
        ->and(Schema::connection('tenant')->hasTable('jobs'))->toBeFalse()
        ->and(DB::connection('central')->table('jobs')->count())->toBe(0);

    Queue::connection('database')->push(new CentralDatabaseQueueProbe);

    expect(DB::connection('central')->table('jobs')->count())->toBe(1);
});
