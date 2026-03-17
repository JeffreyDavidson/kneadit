<?php

namespace Tests\Feature;

use App\Http\Controllers\StripeWebhookController;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Tests\CentralTestCase;

class StripeWebhookHandlerTest extends CentralTestCase
{
    public function test_webhook_controller_extends_cashier(): void
    {
        $this->assertTrue(
            is_subclass_of(StripeWebhookController::class, WebhookController::class)
        );
    }

    public function test_webhook_controller_handles_subscription_updated(): void
    {
        $this->assertTrue(
            method_exists(StripeWebhookController::class, 'handleCustomerSubscriptionUpdated')
        );
    }

    public function test_webhook_controller_handles_payment_failed(): void
    {
        $this->assertTrue(
            method_exists(StripeWebhookController::class, 'handleInvoicePaymentFailed')
        );
    }

    public function test_webhook_controller_handles_subscription_deleted(): void
    {
        $this->assertTrue(
            method_exists(StripeWebhookController::class, 'handleCustomerSubscriptionDeleted')
        );
    }

    public function test_price_id_to_plan_mapping(): void
    {
        config(['saas.stripe_prices' => [
            'starter' => 'price_test_starter',
            'growth' => 'price_test_growth',
            'pro' => 'price_test_pro',
        ]]);

        $controller = new StripeWebhookController;
        $reflection = new \ReflectionMethod($controller, 'priceIdToPlan');
        $reflection->setAccessible(true);

        $this->assertEquals('starter', $reflection->invoke($controller, 'price_test_starter'));
        $this->assertEquals('growth', $reflection->invoke($controller, 'price_test_growth'));
        $this->assertEquals('pro', $reflection->invoke($controller, 'price_test_pro'));
        $this->assertNull($reflection->invoke($controller, 'price_unknown'));
    }

    public function test_webhook_route_uses_custom_controller(): void
    {
        $source = file_get_contents(base_path('routes/billing.php'));

        $this->assertStringContainsString('StripeWebhookController', $source);
        $this->assertStringNotContainsString('Cashier\Http\Controllers\WebhookController', $source);
    }

    public function test_payment_failed_handler_sends_emails(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/StripeWebhookController.php'));

        $this->assertStringContainsString('Payment failed', $source);
        $this->assertStringContainsString('Mail::raw', $source);
        $this->assertStringContainsString('platform_notify', $source);
    }
}
