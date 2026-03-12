<?php

namespace Tests\Feature;

use App\Console\Commands\CheckPayPalPayments;
use App\Console\Commands\SendBirthdayDiscounts;
use App\Console\Commands\SendRepeatOrderReminders;
use Tests\CentralTestCase;

class TenantAwareCommandsTest extends CentralTestCase
{
    public function test_check_paypal_payments_command_exists(): void
    {
        $this->artisan('paypal:check-payments')
            ->assertSuccessful();
    }

    public function test_send_birthday_discounts_command_exists(): void
    {
        $this->artisan('birthday:send-discounts')
            ->assertSuccessful();
    }

    public function test_send_repeat_order_reminders_command_exists(): void
    {
        $this->artisan('orders:send-repeat-reminders')
            ->assertSuccessful();
    }

    public function test_paypal_command_loops_through_tenants(): void
    {
        // Verify the command class uses Tenant model
        $reflection = new \ReflectionClass(CheckPayPalPayments::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString('Tenant::all()', $source);
        $this->assertStringContainsString('tenancy()->initialize', $source);
        $this->assertStringContainsString('Setting::flushCache()', $source);
    }

    public function test_birthday_command_loops_through_tenants(): void
    {
        $reflection = new \ReflectionClass(SendBirthdayDiscounts::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString('Tenant::all()', $source);
        $this->assertStringContainsString('tenancy()->initialize', $source);
        $this->assertStringContainsString('Setting::flushCache()', $source);
    }

    public function test_repeat_reminders_command_loops_through_tenants(): void
    {
        $reflection = new \ReflectionClass(SendRepeatOrderReminders::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString('Tenant::all()', $source);
        $this->assertStringContainsString('tenancy()->initialize', $source);
        $this->assertStringContainsString('Setting::flushCache()', $source);
    }

    public function test_commands_handle_empty_tenant_list(): void
    {
        // With no tenants, commands should succeed without errors
        $this->artisan('paypal:check-payments')->assertSuccessful();
        $this->artisan('birthday:send-discounts')->assertSuccessful();
        $this->artisan('orders:send-repeat-reminders')->assertSuccessful();
    }
}
