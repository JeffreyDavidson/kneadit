<?php

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

beforeEach(fn () => setUpCentralTest());

test('bakeries command exists and is registered', function () {
    $command = new App\Console\Commands\Tenants\CreateDemoBakeriesCommand;

    expect($command->getName())->toBe('tenant:bakeries');
});

test('bakeries command creates five bakeries via subprocess', function () {
    Process::fake([
        '*' => Process::result(output: 'OK'),
    ]);

    pendingArtisan('tenant:bakeries')
        ->expectsOutputToContain('Sweet Dreams Bakery')
        ->expectsOutputToContain('Honeycomb Bakes')
        ->expectsOutputToContain('Flour Power Kitchen')
        ->assertSuccessful();

    Process::assertRanTimes(
        fn (PendingProcess $process): bool => is_string($process->command)
            && str_contains($process->command, 'tenant:create-one'),
        5,
    );
});

test('bakeries command skips existing tenants', function () {
    Process::fake([
        '*' => Process::result(output: 'OK'),
    ]);

    createTenant([
        'id' => 'sweetdreams',
        'name' => 'Sarah Mitchell',
        'email' => 'sarah@sweetdreamsbakery.com',
        'store_name' => 'Sweet Dreams Bakery',
    ]);

    pendingArtisan('tenant:bakeries')
        ->expectsOutputToContain('already exists')
        ->assertSuccessful();

    Process::assertRanTimes(
        fn (PendingProcess $process): bool => is_string($process->command)
            && str_contains($process->command, 'tenant:create-one'),
        4,
    );
});

test('bakeries command with fresh flag deletes existing tenants first', function () {
    Process::fake([
        '*' => Process::result(output: 'OK'),
    ]);

    createTenant([
        'id' => 'sweetdreams',
        'name' => 'Sarah Mitchell',
        'email' => 'sarah@sweetdreamsbakery.com',
        'store_name' => 'Sweet Dreams Bakery',
    ]);

    pendingArtisan('tenant:bakeries', ['--fresh' => true])
        ->expectsOutputToContain('Deleting')
        ->assertSuccessful();

    Process::assertRanTimes(
        fn (PendingProcess $process): bool => is_string($process->command)
            && str_contains($process->command, 'tenant:create-one'),
        5,
    );
});

test('bakeries command handles failed subprocess', function () {
    Process::fake([
        '*' => Process::result(exitCode: 1, errorOutput: 'Database error'),
    ]);

    pendingArtisan('tenant:bakeries')
        ->expectsOutputToContain('Failed')
        ->assertSuccessful();
});

test('bakeries command outputs hosts file instructions', function () {
    Process::fake([
        '*' => Process::result(output: 'OK'),
    ]);

    pendingArtisan('tenant:bakeries')
        ->expectsOutputToContain('/etc/hosts')
        ->expectsOutputToContain('sweetdreams.kneadit.test')
        ->assertSuccessful();
});
