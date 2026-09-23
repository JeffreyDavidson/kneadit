<?php

use App\Console\Commands\Tenants\SeedTenantDatabasesCommand;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Console\Seeds\SeedCommand as LaravelSeedCommand;

test('central and tenant database seed commands keep separate names and options', function () {
    $commands = app(Kernel::class)->all();

    expect($commands)
        ->toHaveKeys(['db:seed', 'tenants:seed'])
        ->and($commands['db:seed'])
        ->toBeInstanceOf(LaravelSeedCommand::class)
        ->and($commands['tenants:seed'])
        ->toBeInstanceOf(SeedTenantDatabasesCommand::class);

    $tenantSeedCommand = $commands['tenants:seed'];

    if (! $tenantSeedCommand instanceof SeedTenantDatabasesCommand) {
        throw new RuntimeException('The tenant seed command was not registered with its Laravel 13 compatibility signature.');
    }

    expect($tenantSeedCommand->getDefinition()->hasOption('tenants'))->toBeTrue();
});
