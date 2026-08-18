<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantDatabasePath;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    File::deleteDirectory(tenantDatabaseSecurityRoot());
    config(['tenancy.tenant_db_path' => tenantDatabaseSecurityRoot()]);
});

afterEach(function () {
    File::deleteDirectory(tenantDatabaseSecurityRoot());
});

function tenantDatabaseSecurityRoot(): string
{
    return storage_path('framework/testing/tenant-database-security-' . getmypid());
}

test('tenant database paths remain inside the configured root', function () {
    $path = resolve(TenantDatabasePath::class)->resolve('tenant-safe_bakery.sqlite');

    expect($path)->toBe(tenantDatabaseSecurityRoot() . DIRECTORY_SEPARATOR . 'tenant-safe_bakery.sqlite');
});

test('tenant database paths reject traversal and path separators', function (string $databaseName) {
    expect(fn () => resolve(TenantDatabasePath::class)->resolve($databaseName))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'parent traversal' => '../central.sqlite',
    'absolute path' => '/tmp/central.sqlite',
    'nested path' => 'nested/tenant.sqlite',
    'Windows traversal' => '..\\central.sqlite',
    'hidden path' => '.tenant.sqlite',
]);

test('tenant databases are created with owner-only permissions', function () {
    $tenant = new Tenant(['id' => 'safe-bakery']);
    $tenant->setInternal('db_name', 'tenantsafebakery');
    $manager = resolve(TenantSQLiteDatabaseManager::class);

    expect($manager->createDatabase($tenant))->toBeTrue();

    $path = tenantDatabaseSecurityRoot() . DIRECTORY_SEPARATOR . 'tenantsafebakery';
    $permissions = fileperms($path);

    throw_if($permissions === false, RuntimeException::class, 'Unable to inspect tenant database permissions.');

    expect($manager->databaseExists('tenantsafebakery'))->toBeTrue()
        ->and($permissions & 0777)->toBe(0600);
});

test('tenant database connections refuse filesystem symlinks', function () {
    File::ensureDirectoryExists(tenantDatabaseSecurityRoot(), 0700);
    $target = storage_path('framework/testing/tenant-database-target-' . Str::uuid());
    File::put($target, 'not a tenant database');
    $link = tenantDatabaseSecurityRoot() . DIRECTORY_SEPARATOR . 'tenantsymlink';
    symlink($target, $link);

    try {
        $manager = resolve(TenantSQLiteDatabaseManager::class);

        expect($manager->databaseExists('tenantsymlink'))->toBeFalse()
            ->and(fn () => $manager->makeConnectionConfig([], 'tenantsymlink'))
            ->toThrow(RuntimeException::class, 'Refusing to connect through a tenant database symlink.');
    } finally {
        if (is_link($link)) {
            unlink($link);
        }

        File::delete($target);
    }
});

test('tenant database connections normalize existing file permissions', function () {
    File::ensureDirectoryExists(tenantDatabaseSecurityRoot(), 0700);
    $path = tenantDatabaseSecurityRoot() . DIRECTORY_SEPARATOR . 'tenantexisting';
    File::put($path, '');
    File::chmod($path, 0644);

    resolve(TenantSQLiteDatabaseManager::class)->makeConnectionConfig([], 'tenantexisting');

    $permissions = fileperms($path);

    throw_if($permissions === false, RuntimeException::class, 'Unable to inspect tenant database permissions.');

    expect($permissions & 0777)->toBe(0600);
});
