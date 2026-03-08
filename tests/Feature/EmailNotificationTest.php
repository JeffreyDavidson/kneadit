<?php

namespace Tests\Feature;

use App\Mail\OrderBaking;
use App\Mail\OrderConfirmed;
use App\Mail\OrderReady;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected Order $order;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }

        $this->user = User::create(['name' => 'Test', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $this->customer = Customer::create(['name' => 'Test Customer', 'email' => 'customer@test.com']);
        $this->order = Order::create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => 'pending',
            'total' => 25.00,
            'subtotal' => 25.00,
        ]);
        // Disable loyalty to isolate email tests
        Setting::set('loyalty_enabled', '0');
    }

    /** @test */
    public function order_confirmed_email_sent_on_status_change(): void
    {
        Mail::fake();

        $this->order->update(['status' => 'confirmed']);

        Mail::assertQueued(OrderConfirmed::class, fn ($mail) => $mail->hasTo('customer@test.com'));
    }

    /** @test */
    public function order_ready_email_sent_on_status_change(): void
    {
        Mail::fake();

        $this->order->update(['status' => 'ready']);

        Mail::assertQueued(OrderReady::class, fn ($mail) => $mail->hasTo('customer@test.com'));
    }

    /** @test */
    public function baking_status_sends_baking_email(): void
    {
        Mail::fake();

        $this->order->update(['status' => 'baking']);

        Mail::assertQueued(OrderBaking::class);
        Mail::assertNotQueued(OrderConfirmed::class);
        Mail::assertNotQueued(OrderReady::class);
    }

    /** @test */
    public function no_email_sent_when_non_status_field_changes(): void
    {
        Mail::fake();

        $this->order->update(['notes' => 'Updated notes']);

        Mail::assertNothingQueued();
    }

    /** @test */
    public function email_contains_correct_order_details(): void
    {
        $mail = new OrderConfirmed($this->order);
        $envelope = $mail->envelope();

        $this->assertStringContains($this->order->order_number, $envelope->subject);
    }

    /** @test */
    public function email_contains_store_name_from_settings(): void
    {
        Setting::set('store_name', 'Sweet Sunrise Bakery');

        $mail = new OrderConfirmed($this->order);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Sweet Sunrise Bakery', $envelope->subject);
    }

    /** @test */
    public function email_uses_default_store_name_when_not_set(): void
    {
        $mail = new OrderConfirmed($this->order);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('KneadIt Bakery', $envelope->subject);
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertStringContainsString($needle, $haystack);
    }
}
