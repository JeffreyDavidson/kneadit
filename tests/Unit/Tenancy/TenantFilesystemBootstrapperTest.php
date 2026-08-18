<?php

use App\Models\Platform\Tenant;
use App\Tenancy\TenantFilesystemBootstrapper;
use Illuminate\Support\Facades\Config;

test('import disk roots are isolated when tenant context bootstraps', function () {
    $bootstrapper = resolve(TenantFilesystemBootstrapper::class);
    $originalRoot = Config::string('filesystems.disks.imports.root');

    try {
        $bootstrapper->bootstrap(new Tenant(['id' => 'bakery-a']));

        expect(Config::string('filesystems.disks.imports.root'))
            ->toBe(storage_path('app/private/csv-imports/tenantbakery-a/'));
    } finally {
        $bootstrapper->revert();
    }

    expect(Config::string('filesystems.disks.imports.root'))->toBe($originalRoot);
});

test('filesystem tenancy rejects unsafe tenant identifiers', function () {
    $bootstrapper = resolve(TenantFilesystemBootstrapper::class);

    expect(fn () => $bootstrapper->bootstrap(new Tenant(['id' => '../escape'])))
        ->toThrow(InvalidArgumentException::class);
});
