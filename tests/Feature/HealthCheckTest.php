<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\CentralTestCase;

class HealthCheckTest extends CentralTestCase
{
    public function test_health_check_command_exists(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $this->artisan('health:check')
            ->assertSuccessful();
    }

    public function test_health_check_verifies_database_connection(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $this->artisan('health:check')
            ->expectsOutputToContain('Database connection OK')
            ->assertSuccessful();
    }

    public function test_health_check_verifies_users_table(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $this->artisan('health:check')
            ->expectsOutputToContain('Users table OK')
            ->assertSuccessful();
    }

    public function test_health_check_verifies_disk_space(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $this->artisan('health:check')
            ->expectsOutputToContain('Disk space OK')
            ->assertSuccessful();
    }

    public function test_health_check_verifies_storage_writable(): void
    {
        Http::fake(['*' => Http::response('OK', 200)]);

        $this->artisan('health:check')
            ->expectsOutputToContain('Storage/logs writable')
            ->assertSuccessful();
    }

    public function test_health_check_detects_homepage_failure(): void
    {
        Http::fake(['*' => Http::response('Server Error', 500)]);

        $this->artisan('health:check')
            ->assertFailed();
    }
}
