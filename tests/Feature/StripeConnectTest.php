<?php

namespace Tests\Feature;

use App\Http\Controllers\StripeConnectController;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConnectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
    }

    /** @test */
    public function get_account_status_returns_null_when_no_connect_id(): void
    {
        $this->assertNull(StripeConnectController::getAccountStatus());
    }

    /** @test */
    public function get_account_status_returns_null_when_connect_id_is_empty(): void
    {
        Setting::set('stripe_connect_id', '');

        // Empty string is falsy, should return null
        $this->assertNull(StripeConnectController::getAccountStatus());
    }

    /** @test */
    public function get_account_status_returns_null_when_stripe_api_fails(): void
    {
        Setting::set('stripe_connect_id', 'acct_invalid_12345');

        // With no valid Stripe key configured, this should catch the exception and return null
        config(['cashier.secret' => 'sk_test_fake_key']);

        $result = StripeConnectController::getAccountStatus();

        $this->assertNull($result);
    }
}
