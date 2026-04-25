<?php

use App\Console\Commands\Tenants\DoctorTenantsCommand;

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

    test()->artisan('tenants:doctor')
        ->expectsOutputToContain('Healthy:        0')
        ->expectsOutputToContain('Orphan rows:    0')
        ->expectsOutputToContain('Orphan files:   0')
        ->expectsOutputToContain('No drift detected.')
        ->assertSuccessful();
});
