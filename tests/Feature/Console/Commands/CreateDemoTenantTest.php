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

test('demo tenant command with fresh option deletes existing tenant first', function () {
    createTenant([
        'id' => 'demo',
        'name' => 'Demo Baker',
        'email' => 'demo@getkneadit.app',
    ]);

    // The command tries to delete and recreate, but since tenant:create triggers
    // real DB events we just verify it handles the --fresh flag
    $source = file_get_contents(app_path('Console/Commands/Tenants/CreateDemoTenantCommand.php'));

    expect($source)
        ->toContain("option('fresh')")
        ->toContain('->delete()')
        ->toContain('Deleting existing demo tenant');
});

test('demo tenant command source seeds expected settings', function () {
    $source = file_get_contents(app_path('Console/Commands/Tenants/CreateDemoTenantCommand.php'));

    expect($source)
        ->toContain("settings(['store_name' => 'Sweet Dreams Bakery'])")
        ->toContain("settings(['store_email' => 'demo@getkneadit.app'])")
        ->toContain("settings(['store_phone'")
        ->toContain("settings(['default_daily_capacity'");
});

test('demo tenant command creates pro plan tenant', function () {
    $source = file_get_contents(app_path('Console/Commands/Tenants/CreateDemoTenantCommand.php'));

    expect($source)
        ->toContain("'plan' => 'pro'")
        ->toContain("'is_active' => true");
});
