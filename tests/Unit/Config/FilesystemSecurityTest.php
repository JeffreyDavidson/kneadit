<?php

use App\Tenancy\TenantFilesystemBootstrapper;
use Illuminate\Support\Facades\Config;

test('application filesystem disks fail loudly on storage errors', function (string $disk) {
    expect(Config::get("filesystems.disks.{$disk}.throw"))->toBeTrue();
})->with(['local', 'imports', 'public', 's3']);

test('CSV imports use a private non-servable tenant-aware disk', function () {
    expect(Config::get('filesystems.disks.imports.root'))
        ->toBe(storage_path('app/private/csv-imports'))
        ->and(Config::get('filesystems.disks.imports.visibility'))->toBe('private')
        ->and(Config::get('filesystems.disks.imports.serve'))->toBeFalse()
        ->and(Config::array('tenancy.filesystem.disks'))->toContain('imports')
        ->and(Config::get('tenancy.filesystem.root_override.imports'))
        ->toBe('%storage_path%/app/private/csv-imports/tenant%tenant%/')
        ->and(Config::array('tenancy.bootstrappers'))->toContain(TenantFilesystemBootstrapper::class)
        ->and(Config::get('tenancy.filesystem.suffix_storage_path'))->toBeFalse();
});
