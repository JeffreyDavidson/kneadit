<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Console\Seeds\SeedCommand as LaravelSeedCommand;
use Stancl\Tenancy\Commands\Seed as StanclSeedCommand;

test('central and tenant database seed commands keep separate names and options', function () {
    $commands = app(Kernel::class)->all();

    expect($commands)
        ->toHaveKeys(['db:seed', 'tenants:seed'])
        ->and($commands['db:seed'])
        ->toBeInstanceOf(LaravelSeedCommand::class)
        ->and($commands['tenants:seed'])
        ->toBeInstanceOf(StanclSeedCommand::class)
        ->and($commands['tenants:seed']->getDefinition()->hasOption('tenants'))
        ->toBeTrue();
});
