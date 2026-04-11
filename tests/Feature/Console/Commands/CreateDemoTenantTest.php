<?php

use App\Console\Commands\Tenants\CreateDemoTenantCommand;

beforeEach(fn () => setUpCentralTest());

test('demo tenant command exists and is registered', function () {
    $command = new CreateDemoTenantCommand;

    expect($command->getName())->toBe('tenant:demo');
});

test('demo tenant warns when tenant already exists', function () {
    createTenant([
        'id' => 'demo',
        'name' => 'Demo Baker',
        'email' => 'demo@getkneadit.app',
    ]);

    $this->artisan('tenant:demo')
        ->expectsOutputToContain('already exists')
        ->assertSuccessful();
});

test('demo tenant command accepts fresh option', function () {
    $command = new CreateDemoTenantCommand;

    expect($command->getDefinition()->hasOption('fresh'))->toBeTrue();
});

test('demo tenant command source creates correct domain entries', function () {
    $source = file_get_contents(app_path('Console/Commands/Tenants/CreateDemoTenantCommand.php'));

    expect($source)
        ->toContain("'domain' => \$domain")
        ->toContain("'domain' => \$subdomain");
});
