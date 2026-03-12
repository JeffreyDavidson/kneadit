<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodsTest extends TestCase
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
    public function payment_step_stores_payment_methods_as_json_array(): void
    {
        $page = new \App\Filament\Pages\Onboarding;
        $page->payment_methods = ['cash', 'paypal', 'stripe'];
        $page->paypal_client_id = 'test_id';
        $page->paypal_client_secret = 'test_secret';
        $page->paypal_sandbox = true;

        $reflection = new \ReflectionMethod($page, 'savePaymentStep');
        $reflection->invoke($page);

        $stored = Setting::get('payment_methods');
        $this->assertNotNull($stored);

        $decoded = json_decode($stored, true);
        $this->assertIsArray($decoded);
        $this->assertContains('cash', $decoded);
        $this->assertContains('paypal', $decoded);
        $this->assertContains('stripe', $decoded);
    }

    /** @test */
    public function payment_step_sets_legacy_payment_method_to_first_value(): void
    {
        $page = new \App\Filament\Pages\Onboarding;
        $page->payment_methods = ['stripe', 'cash'];
        $page->paypal_client_id = '';
        $page->paypal_client_secret = '';
        $page->paypal_sandbox = false;

        $reflection = new \ReflectionMethod($page, 'savePaymentStep');
        $reflection->invoke($page);

        $this->assertEquals('stripe', Setting::get('payment_method'));
    }

    /** @test */
    public function payment_step_defaults_to_cash_when_empty(): void
    {
        $page = new \App\Filament\Pages\Onboarding;
        $page->payment_methods = [];
        $page->paypal_client_id = '';
        $page->paypal_client_secret = '';
        $page->paypal_sandbox = false;

        $reflection = new \ReflectionMethod($page, 'savePaymentStep');
        $reflection->invoke($page);

        $this->assertEquals('cash', Setting::get('payment_method'));
    }

    /** @test */
    public function payment_methods_property_defaults_to_cash(): void
    {
        $page = new \App\Filament\Pages\Onboarding;

        $this->assertEquals(['cash'], $page->payment_methods);
    }
}
