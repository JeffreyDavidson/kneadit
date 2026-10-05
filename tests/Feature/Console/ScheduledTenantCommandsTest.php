<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Tenancy;

/**
 * Tenant tables only exist in each bakery's own database, never in the
 * central one. A scheduled command that reads tenant data before it has
 * entered a tenant would fail on production with "no such table".
 *
 * @return list<string>
 */
function tenantOnlyTables(): array
{
    $created = collect(File::files(database_path('migrations/tenant')))
        ->flatMap(function (SplFileInfo $file): array {
            preg_match_all("/Schema::create\\('([a-z_]+)'/", File::get($file->getPathname()), $matches);

            return $matches[1];
        })
        ->unique();

    return $created->diff(Schema::getTableListing(schemaQualified: false))->values()->all();
}

/** @return list<string> */
function scheduledCommandSignatures(): array
{
    return collect(app(Schedule::class)->events())
        ->map(fn (Event $event): string => str($event->command)->after("'artisan' ")->before(' ')->toString())
        ->values()
        ->all();
}

test('every scheduled command is covered by the central-database guard', function () {
    $tenantAware = [
        'paypal:check-payments',
        'birthday:send-emails',
        'orders:send-repeat-reminders',
        'digest:weekly',
        'reviews:send-requests',
        'inventory:send-low-stock-alert',
        'carts:send-abandonment-emails',
        'webhooks:prune',
        'analytics:prune-page-views',
        'campaigns:send-scheduled',
    ];
    $centralOnly = [
        'checkins:send',
        'churn:check',
        'backup:databases',
        'health:check',
        'trial:check',
        'platform:audit-free-forever',
        'platform:prune-expired-tokens',
        'tenants:verify-custom-domains',
        'tenants:sync-onboarding-metrics',
        'platform:send-scheduled-campaigns',
    ];

    expect(array_diff(scheduledCommandSignatures(), $tenantAware, $centralOnly))->toBeEmpty();
});

test('a scheduled tenant command never reads tenant tables before entering a tenant', function (string $signature) {
    config(['services.paypal.client_id' => 'platform-client-id']);
    setUpCentralOnlyTest();
    $tenantTables = tenantOnlyTables();
    createTenant(['id' => 'bakery-a', 'email' => 'a@example.com']);
    $inTenant = false;

    $tenancy = Mockery::mock(Tenancy::class);
    $tenancy->shouldReceive('initialize')->andReturnUsing(function () use (&$inTenant): void {
        $inTenant = true;

        createTenantTablesOnce();
    });
    $tenancy->shouldReceive('end')->andReturnUsing(function () use (&$inTenant): void {
        $inTenant = false;
    });
    app()->instance(Tenancy::class, $tenancy);

    $outsideTenant = [];
    DB::listen(function (QueryExecuted $query) use (&$inTenant, &$outsideTenant, $tenantTables): void {
        if ($inTenant) {
            return;
        }

        foreach ($tenantTables as $table) {
            if (str_contains($query->sql, "\"{$table}\"")) {
                $outsideTenant[] = $query->sql;
            }
        }
    });

    try {
        Artisan::call($signature);
    } catch (Throwable $e) {
        $outsideTenant[] = $e->getMessage();
    }

    expect($outsideTenant)->toBeEmpty();
})->with([
    'paypal:check-payments',
    'birthday:send-emails',
    'orders:send-repeat-reminders',
    'digest:weekly',
    'reviews:send-requests',
    'inventory:send-low-stock-alert',
    'carts:send-abandonment-emails',
    'webhooks:prune',
    'analytics:prune-page-views',
    'campaigns:send-scheduled',
]);
