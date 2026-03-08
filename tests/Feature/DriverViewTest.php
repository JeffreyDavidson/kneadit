<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
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

    private function createOrder(array $attrs = []): Order
    {
        $user = User::create(['name' => 'Baker', 'email' => 'baker@test.com', 'password' => bcrypt('pass')]);
        $customer = Customer::create(['name' => 'Test Customer', 'email' => 'cust@test.com']);

        return Order::create(array_merge([
            'order_number' => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
            'subtotal' => 25.00,
            'total' => 25.00,
            'delivery_address' => '123 Main St',
            'requested_date' => today(),
        ], $attrs));
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
        $order = $this->createOrder(['order_number' => 'ORD-001']);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
        $response->assertSee('ORD-001');
    }

    /** @test */
    public function driver_page_hides_pickup_orders(): void
    {
        Setting::set('store_name', 'Test Bakery');
        $this->createOrder(['order_number' => 'ORD-PICKUP', 'delivery_address' => '']);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
        $response->assertDontSee('ORD-PICKUP');
    }

    /** @test */
    public function driver_page_hides_past_orders(): void
    {
        Setting::set('store_name', 'Test Bakery');
        $this->createOrder(['order_number' => 'ORD-OLD', 'requested_date' => today()->subDay()]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/driver');
        $response->assertOk();
        $response->assertDontSee('ORD-OLD');
    }

    /** @test */
    public function mark_delivered_changes_order_status(): void
    {
        $order = $this->createOrder(['status' => 'ready']);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post("/driver/{$order->id}/delivered");
        $response->assertRedirect();

        $this->assertEquals('delivered', $order->fresh()->status);
    }

    /** @test */
    public function mark_delivered_redirects_back(): void
    {
        $order = $this->createOrder(['status' => 'ready']);

        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->from('/driver')
            ->post("/driver/{$order->id}/delivered");

        $response->assertRedirect('/driver');
    }
}
