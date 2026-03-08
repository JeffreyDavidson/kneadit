<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class DriverViewTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantMiddleware;

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
        ];
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function driver_page_loads(): void
    {
        Setting::set('store_name', 'Test Bakery');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
    }

    /** @test */
    public function driver_page_shows_todays_delivery_orders(): void
    {
        Setting::set('store_name', 'Test Bakery');
        $customer = $this->createCustomer();

        Order::create([
            'order_number' => 'ORD-001',
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'delivery_address' => '123 Main St',
            'requested_date' => today(),
            'total' => 25.00,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
        $response->assertSee('ORD-001');
    }

    /** @test */
    public function driver_page_hides_pickup_orders(): void
    {
        Setting::set('store_name', 'Test Bakery');
        $customer = $this->createCustomer();

        // Pickup order (no delivery address)
        Order::create([
            'order_number' => 'ORD-PICKUP',
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'delivery_address' => '',
            'requested_date' => today(),
            'total' => 15.00,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
        $response->assertDontSee('ORD-PICKUP');
    }

    /** @test */
    public function driver_page_hides_past_orders(): void
    {
        Setting::set('store_name', 'Test Bakery');
        $customer = $this->createCustomer();

        Order::create([
            'order_number' => 'ORD-OLD',
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'delivery_address' => '456 Oak Ave',
            'requested_date' => today()->subDay(),
            'total' => 30.00,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
        $response->assertDontSee('ORD-OLD');
    }

    /** @test */
    public function mark_delivered_changes_order_status(): void
    {
        $customer = $this->createCustomer();

        $order = Order::create([
            'order_number' => 'ORD-DEL',
            'customer_id' => $customer->id,
            'status' => 'ready',
            'delivery_address' => '789 Elm St',
            'requested_date' => today(),
            'total' => 20.00,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post("/driver/{$order->id}/delivered");
        $response->assertRedirect();

        $this->assertEquals('delivered', $order->fresh()->status);
    }

    /** @test */
    public function mark_delivered_redirects_back(): void
    {
        $customer = $this->createCustomer();

        $order = Order::create([
            'order_number' => 'ORD-BACK',
            'customer_id' => $customer->id,
            'status' => 'ready',
            'delivery_address' => '101 Pine St',
            'requested_date' => today(),
            'total' => 18.00,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->from('/driver')
            ->post("/driver/{$order->id}/delivered");

        $response->assertRedirect('/driver');
    }
}
