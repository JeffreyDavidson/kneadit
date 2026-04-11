<?php

use App\Console\Commands\Tenants\CreateOneTenantCommand;

test('create-one tenant command exists and is hidden', function () {
    $command = new CreateOneTenantCommand;

    expect($command->getName())->toBe('tenant:create-one')
        ->and($command->isHidden())->toBeTrue();
});

test('create-one tenant command requires all arguments', function () {
    $command = new CreateOneTenantCommand;
    $definition = $command->getDefinition();

    expect($definition->hasArgument('id'))->toBeTrue()
        ->and($definition->hasArgument('name'))->toBeTrue()
        ->and($definition->hasArgument('email'))->toBeTrue()
        ->and($definition->hasArgument('store_name'))->toBeTrue()
        ->and($definition->hasArgument('brand_primary'))->toBeTrue()
        ->and($definition->hasArgument('brand_secondary'))->toBeTrue();
});

test('create-one tenant command arguments are required', function () {
    $command = new CreateOneTenantCommand;
    $definition = $command->getDefinition();

    expect($definition->getArgument('id')->isRequired())->toBeTrue()
        ->and($definition->getArgument('name')->isRequired())->toBeTrue()
        ->and($definition->getArgument('email')->isRequired())->toBeTrue()
        ->and($definition->getArgument('store_name')->isRequired())->toBeTrue()
        ->and($definition->getArgument('brand_primary')->isRequired())->toBeTrue()
        ->and($definition->getArgument('brand_secondary')->isRequired())->toBeTrue();
});

test('create-one tenant command source creates domains and seeds data', function () {
    $source = file_get_contents(app_path('Console/Commands/Tenants/CreateOneTenantCommand.php'));

    expect($source)
        ->toContain('domains()->create')
        ->toContain('tenants:migrate')
        ->toContain('db:seed');
});
