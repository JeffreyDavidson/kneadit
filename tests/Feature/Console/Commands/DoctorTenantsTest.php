<?php

use App\Console\Commands\Tenants\DoctorTenantsCommand;
use Illuminate\Support\Facades\File;

test('tenants:doctor command exists with the expected signature', function () {
    $command = new DoctorTenantsCommand;
    $definition = $command->getDefinition();

    expect($command->getName())->toBe('tenants:doctor')
        ->and($definition->hasOption('fix'))->toBeTrue()
        ->and($definition->hasOption('force'))->toBeTrue()
        ->and($definition->hasOption('seed'))->toBeTrue();
});

test('tenants:doctor reports clean when no tenants and no files exist', function () {
    setUpCentralTest();

    // Point database_path() at an empty temp dir so the command's
    // glob('tenant*') scan doesn't pick up the developer's local
    // tenantbrowser-test / tenantdemo SQLite files.
    $original = app()->databasePath();
    $isolated = sys_get_temp_dir() . '/kneadit-doctor-test-' . uniqid();
    File::ensureDirectoryExists($isolated);
    app()->useDatabasePath($isolated);

    try {
        pendingArtisan('tenants:doctor')
            ->expectsOutputToContain('Healthy:        0')
            ->expectsOutputToContain('Orphan rows:    0')
            ->expectsOutputToContain('Orphan files:   0')
            ->expectsOutputToContain('No drift detected.')
            ->assertSuccessful();
    } finally {
        app()->useDatabasePath($original);
        File::deleteDirectory($isolated);
    }
});
