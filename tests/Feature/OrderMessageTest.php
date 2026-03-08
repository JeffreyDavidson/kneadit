<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Mail\NewOrderMessage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class OrderMessageTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantMiddleware;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->tenantMiddleware = [
            InitializeTenancyByDomainOrSubdomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureStorefrontEnabled::class,
            TrackPageView::class,
        ];

        $user = User::create(['name' => 'Baker', 'email' => 'baker@test.com', 'password' => bcrypt('pass')]);
        $customer = Customer::create(['name' => 'Test', 'email' => 'test@example.com']);
        $this->order = Order::create([
            'order_number' => 'ORD-MSG-001',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
            'subtotal' => 50.00,
            'total' => 50.00,
        ]);
    }

    /** @test */
    public function messages_endpoint_returns_order_messages(): void
    {
        OrderMessage::create([
            'order_id' => $this->order->id,
            'sender_type' => 'baker',
            'sender_name' => 'Baker Bob',
            'message' => 'Your order is being prepared!',
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->getJson("/order/{$this->order->id}/messages");

        $response->assertOk();
        $response->assertJsonPath('messages.0.message', 'Your order is being prepared!');
    }

    /** @test */
    public function customer_can_send_message_on_their_order(): void
    {
        Mail::fake();

        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->postJson("/order/{$this->order->id}/messages", [
                'message' => 'Can I add extra frosting?',
                'sender_name' => 'Test Customer',
                'sender_email' => 'test@example.com',
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('order_messages', [
            'order_id' => $this->order->id,
            'message' => 'Can I add extra frosting?',
        ]);
    }

    /** @test */
    public function message_is_saved_with_correct_sender_type(): void
    {
        Mail::fake();

        $this->withoutMiddleware($this->tenantMiddleware)
            ->postJson("/order/{$this->order->id}/messages", [
                'message' => 'Hello!',
                'sender_name' => 'Customer',
                'sender_email' => 'cust@example.com',
            ]);

        $msg = OrderMessage::first();
        $this->assertEquals('customer', $msg->sender_type);
    }

    /** @test */
    public function messages_require_content(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->postJson("/order/{$this->order->id}/messages", [
                'sender_name' => 'Test',
                'sender_email' => 'test@example.com',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['message']);
    }

    /** @test */
    public function notification_email_is_sent_to_baker(): void
    {
        Mail::fake();
        Setting::set('store_email', 'baker@bakery.com');

        $this->withoutMiddleware($this->tenantMiddleware)
            ->postJson("/order/{$this->order->id}/messages", [
                'message' => 'Question about my order',
                'sender_name' => 'Customer',
                'sender_email' => 'cust@example.com',
            ]);

        Mail::assertQueued(NewOrderMessage::class);
    }
}
