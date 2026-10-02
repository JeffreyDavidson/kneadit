<?php

use App\Models\Platform\Tenant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\PendingCommand;

use function Pest\Laravel\artisan;

beforeEach(function () {
    setUpCentralTest();
    config(['services.forge.server_ip' => '203.0.113.10']);
    Date::setTestNow('2026-10-01 09:30');
});

function verifyCustomDomainsCommand(): PendingCommand
{
    $command = artisan('tenants:verify-custom-domains');

    throw_unless($command instanceof PendingCommand, RuntimeException::class, 'Console output must be mocked.');

    return $command;
}

test('command verifies a domain that points at the server', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com']);
    Log::shouldReceive('info')->once()->with('Custom domain verified.', ['tenant' => $tenant->id, 'domain' => 'shop.example.com']);

    verifyCustomDomainsCommand()
        ->expectsOutput('Custom domains checked: 1; verified: 1; unverified: 0; changed: 1.')
        ->assertSuccessful();

    expect($tenant->refresh()->custom_domain_verified_at->toDateTimeString())->toBe('2026-10-01 09:30:00');
});

test('command clears the verification and logs when DNS stopped pointing at the server', function () {
    fakeDnsRecords(['shop.example.com' => '198.51.100.7']);
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => now()->subDay()]);
    Log::shouldReceive('warning')->once()->with('Custom domain no longer verified.', ['tenant' => $tenant->id, 'domain' => 'shop.example.com']);

    verifyCustomDomainsCommand()
        ->expectsOutput('Custom domains checked: 1; verified: 0; unverified: 1; changed: 1.')
        ->assertSuccessful();

    expect($tenant->refresh()->custom_domain_verified_at)->toBeNull();
});

test('command leaves a domain whose state did not change alone and logs nothing', function () {
    fakeDnsRecords(['shop.example.com' => '203.0.113.10']);
    $verifiedAt = now()->subDay();
    $tenant = Tenant::factory()->create(['custom_domain' => 'shop.example.com', 'custom_domain_verified_at' => $verifiedAt]);
    Log::shouldReceive('info', 'warning')->never();

    verifyCustomDomainsCommand()
        ->expectsOutput('Custom domains checked: 1; verified: 1; unverified: 0; changed: 0.')
        ->assertSuccessful();

    expect($tenant->refresh()->custom_domain_verified_at->equalTo($verifiedAt))->toBeTrue();
});

test('command ignores bakeries without a custom domain', function () {
    fakeDnsRecords([]);
    $tenant = Tenant::factory()->create(['custom_domain' => null, 'custom_domain_verified_at' => now()->subDay()]);

    verifyCustomDomainsCommand()
        ->expectsOutput('Custom domains checked: 0; verified: 0; unverified: 0; changed: 0.')
        ->assertSuccessful();

    expect($tenant->refresh()->custom_domain_verified_at)->not->toBeNull();
});

test('command is scheduled daily at a fixed UTC time', function () {
    $event = collect(resolve(Schedule::class)->events())
        ->first(fn ($event): bool => str_contains((string) $event->command, 'tenants:verify-custom-domains'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('30 5 * * *');
});
